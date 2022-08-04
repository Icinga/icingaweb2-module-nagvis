<?php

use Icinga\Module\Icingadb\Common\Database;
use Icinga\Module\Icingadb\Model\Host;
use Icinga\Module\Icingadb\Model\Hostgroup;
use Icinga\Module\Icingadb\Model\Instance;
use Icinga\Module\Icingadb\Model\Service;
use Icinga\Module\Icingadb\Model\Servicegroup;
use Icinga\Module\Icingadb\Redis\VolatileStateResults;
use Icinga\Module\Nagvis\Model\HostgroupSummary;
use Icinga\Module\Nagvis\Model\ServicegroupSummary;
use Icinga\Module\Nagvis\Model\ServicestateSummary;
use ipl\Orm\Query;
use ipl\Orm\UnionQuery;
use ipl\Stdlib\Filter;
use ipl\Stdlib\Str;
use ipl\Web\Filter\QueryString;

class GlobalBackendicingadb implements GlobalBackendInterface
{
    use Database;

    const HOST_SERVICE_SEPARATOR = '~~';

    private static $validConfig = [
        'dbhost'               => [
            'must'     => 1,
            'editable' => 1,
            'default'  => 'localhost',
            'match'    => MATCH_STRING_NO_SPACE
        ],
        'dbport'               => [
            'must'     => 0,
            'editable' => 1,
            'default'  => '3306',
            'match'    => MATCH_INTEGER
        ],
        'dbname'               => [
            'must'     => 1,
            'editable' => 1,
            'default'  => 'icingadb',
            'match'    => MATCH_STRING_NO_SPACE
        ],
        'dbuser'               => [
            'must'     => 1,
            'editable' => 1,
            'default'  => 'icingadb',
            'match'    => MATCH_STRING_NO_SPACE
        ],
        'dbpass'               => [
            'must'     => 0,
            'editable' => 1,
            'default'  => 'icingadb',
            'match'    => MATCH_STRING_EMPTY
        ],
        'dbinstancename'       => [
            'must'     => 0,
            'editable' => 1,
            'default'  => 'default',
            'match'    => MATCH_STRING_NO_SPACE
        ],
        'maxtimewithoutupdate' => [
            'must'     => 0,
            'editable' => 1,
            'default'  => '180',
            'match'    => MATCH_INTEGER
        ],
    ];

    public function __construct($backendId)
    {
    }

    public static function getValidConfig()
    {
        return self::$validConfig;
    }

    public function getProgramStart()
    {
        $query = Instance::on($this->getDb());
        $query->columns('icinga2_start_time');

        if (($instance = $query->first())) {
            return $instance->icinga2_start_time;
        }

        return -1;
    }

    public function getHostNamesProblematic(): array
    {
        $result = [];
        $query = Host::on($this->getDb())
            ->utilize('state')
            ->utilize('service')
            ->utilize('service.state')
            ->columns(['name', 'state.soft_state', 'service.state.soft_state'])
            ->filter(
                Filter::any(
                    Filter::greaterThan('state.soft_state', 0),
                    Filter::greaterThan('service.state.soft_state', 0)
                )
            );

        $query->getSelectBase()->groupBy(['name', 'host.display_name']);

        foreach ($query as $host) {
            $result[] = $host->name;
        }

        return $result;
    }

    public function getObjectsEx($type): array
    {
        return $this->getObjects($type);
    }

    public function getObjects($type, $name1Pattern = '', $name2Pattern = '')
    {
        $result = [];
        $filter = Filter::all();

        switch ($type) {
            case 'host':
                $query = Host::on($this->getDb());
                $query->columns(['name', 'name_ci']);
                break;
            case 'service':
                $query = Service::on($this->getDb())->with(['host']);
                $query->columns(['name', 'name_ci', 'host.name']);
                break;
            case 'hostgroup':
                $query = Hostgroup::on($this->getDb());
                $query->columns(['name', 'name_ci' => 'display_name']);
                break;
            case 'servicegroup':
                $query = Servicegroup::on($this->getDb());
                $query->columns(['name', 'name_ci' => 'display_name']);
                break;
            default:
                return $result;
        }

        if ($name1Pattern !== '') {
            $nameFilter = Filter::equal('name', $name1Pattern);
            if ($type === 'service') {
                $nameFilter = Filter::equal('host.name', $name1Pattern);
            }

            $filter->add($nameFilter);

            if ($name2Pattern !== '') {
                $filter->add(Filter::equal('name', $name2Pattern));
            }
        }

        $query->filter($filter);

        foreach ($query as $item) {
            $result[] = [
                'name1' => $item instanceof Service ? $item->host->name : $item->name,
                'name2' => $item instanceof Service ? $item->name : $item->name_ci,
            ];
        }

        return $result;
    }

    public function getHostState($objects, $options, $filters, $isMemberQuery = false)
    {
        $query = Host::on($this->getDb())->with(['state', 'downtime', 'notes_url']);
        $query->getWith()['host.notes_url']->setJoinType('LEFT');
        $query->setResultSetClass(VolatileStateResults::class);

        $this->parseFilter($query, $objects, $filters, $isMemberQuery, false, HOST_QUERY);

        $results = [];
        foreach ($query as $item) {
            $output = $item->state->output;
            switch ($item->state->soft_state) {
                case 0:
                    $state = UP;
                    break;
                case 1:
                    $state = DOWN;
                    break;
                case 2:
                    $state = UNREACHABLE;
                    break;
                case 3:
                    $state = UNKNOWN;
                    break;
                case 99:
                    $state = UNCHECKED;
                    $output = l('hostIsPending', ['HOST' => $item->name]);
                    break;
                default:
                    $state = UNKNOWN;
                    $output = 'GlobalBackendicingadb::getHostState: Undefined state!';
            }

            $results[$item->name] = [
                $state,
                $output,
                $item->state->is_acknowledged ? 1 : 0,
                $item->state->in_downtime ? 1 : 0,
                0,
                $item->state->state_type === 'hard' ? 1 : 0,
                $item->state->check_attempt,
                $item->max_check_attempts,
                $item->state->last_update,
                $item->state->next_check,
                $item->state->last_state_change,
                $item->state->last_state_change,
                $item->state->performance_data,
                $item->display_name,
                $item->name_ci,
                $item->address,
                $item->notes_url->notes_url,
                $item->checkcommand_name,
                null, // Custom vars
                $item->downtime->author,
                $item->downtime->comment,
                $item->downtime->scheduled_start_time,
                $item->downtime->scheduled_end_time
            ];
        }

        return $results;
    }

    public function getServiceState($objects, $options, $filters, $isMemberQuery = false)
    {
        $query = Service::on($this->getDb())->with(['state', 'downtime', 'notes_url', 'host', 'host.state']);
        $query->getWith()['service.notes_url']->setJoinType('LEFT');
        $query->setResultSetClass(VolatileStateResults::class);

        $this->parseFilter($query, $objects, $filters, $isMemberQuery, false, ! HOST_QUERY);

        $results = [];
        foreach ($query as $item) {
            $output = $item->state->output;
            switch ($item->state->soft_state) {
                case 0:
                    $state = OK;
                    break;
                case 1:
                    $state = WARNING;
                    break;
                case 2:
                    $state = CRITICAL;
                    break;
                case 3:
                    $state = UNKNOWN;
                    break;
                case 99:
                    $state = PENDING;
                    $output = l('serviceNotChecked', ['SERVICE', $item->name_ci]);
                    break;
                default:
                    $state = UNKNOWN;
                    $output = 'GlobalBackendicingadb::getServiceState: Undefined state!';
            }

            $isAcknowledged = $item->state->is_acknowledged;
            if (! $isAcknowledged && $state !== OK && $state !== PENDING) {
                $isAcknowledged = $item->host->state->is_acknowledged;
            }

            $data = [
                $state,
                $output,
                $isAcknowledged ? 1 : 0,
                $item->state->in_downtime ? 1 : 0,
                0,
                $item->state->state_type === 'hard' ? 1 : 0,
                $item->state->check_attempt,
                $item->max_check_attempts,
                $item->state->last_update,
                $item->state->next_check,
                $item->state->last_state_change,
                $item->state->last_state_change,
                $item->state->performance_data,
                $item->display_name,
                $item->name_ci, // Alias
                $item->host->address,
                $item->notes_url->notes_url,
                $item->checkcommand_name,
                null, // Custom vars
                $item->downtime->author,
                $item->downtime->comment,
                $item->downtime->scheduled_start_time,
                $item->downtime->scheduled_end_time,
                $item->name
            ];

            $key = $item->host->name . self::HOST_SERVICE_SEPARATOR . $item->name;
            if (isset($objects[$key])) {
                $results[$key] = $data;
            } else {
                if (! isset($results[$item->host->name])) {
                    $results[$item->host->name] = [];
                }

                $results[$item->host->name][] = $data;
            }
        }

        return $results;
    }

    public function getHostMemberCounts($objects, $options, $filters)
    {
        $query = ServicestateSummary::on($this->getDb())
            ->utilize('state')
            ->utilize('host')
            ->withColumns([
                'host_name'   => 'host.name',
                'host_nameci' => 'host.name_ci'
            ]);

        $query->getSelectBase()->groupBy([
            'service_host.id',
            'host_nameci',
            'host_name'
        ]);
        $this->parseFilter($query, $objects, $filters, MEMBER_QUERY, COUNT_QUERY, ! HOST_QUERY);

        foreach ($query as $item) {
            $result[$item->host_name] = [
                // This causes an undefined array key "0" error somewhere in "NagVisHost" class, so I'm commenting
                // this out as "GlobalBackendPDO" class is doing the same!
                // 'details' => [ALIAS => $item->host_nameci],
                'counts' => [
                    PENDING  => [
                        'normal' => intval($item->services_pending)
                    ],
                    OK       => [
                        'normal'   => intval($item->services_ok),
                        'stale'    => 0,
                        'downtime' => intval($item->services_ok_downtime)
                    ],
                    WARNING  => [
                        'normal'   => intval($item->services_warning),
                        'stale'    => 0,
                        'ack'      => intval($item->services_warning_ack),
                        'downtime' => intval($item->services_warning_downtime)
                    ],
                    CRITICAL => [
                        'normal'   => intval($item->services_critical),
                        'stale'    => 0,
                        'ack'      => intval($item->services_critical_ack),
                        'downtime' => intval($item->services_critical_downtime)
                    ],
                    UNKNOWN  => [
                        'normal'   => intval($item->services_unknown),
                        'stale'    => 0,
                        'ack'      => intval($item->services_unknown_ack),
                        'downtime' => intval($item->services_unknown_downtime)
                    ]
                ]
            ];
        }

        return $result;
    }

    public function getHostgroupStateCounts($objects, $options, $filters)
    {
        $query = HostgroupSummary::on($this->getDb());
        $this->parseFilter($query, $objects, $filters, MEMBER_QUERY, COUNT_QUERY, HOST_QUERY);

        return $this->getGroupStateCounts($query);
    }

    public function getServicegroupStateCounts($objects, $options, $filters)
    {
        $query = ServicegroupSummary::on($this->getDb());
        $this->parseFilter($query, $objects, $filters, MEMBER_QUERY, COUNT_QUERY, ! HOST_QUERY);

        return $this->getGroupStateCounts($query);
    }

    public function getHostNamesWithNoParent()
    {
        // As Icinga DB doesn't support dependencies at the moment, just get all available hosts
        $query = Host::on($this->getDb());
        $query->columns(['name']);

        $results = [];
        foreach ($query as $item) {
            $results[] = $item->name;
        }

        return $results;
    }

    public function getDirectChildNamesByHostName($hostName)
    {
        // FIXME: Implement me once https://github.com/Icinga/icingadb/issues/347 is closed
        return [];
    }

    public function getDirectChildDependenciesNamesByHostName($hostName, $minBusinessImpact = false): array
    {
        return $this->getDirectChildNamesByHostName($hostName);
    }

    public function getDirectParentNamesByHostName($hostName)
    {
        // FIXME: Implement me once https://github.com/Icinga/icingadb/issues/347 is closed
        return [];
    }

    public function getDirectParentDependenciesNamesByHostName($hostName, $minBusinessImpact = false): array
    {
        return $this->getDirectParentNamesByHostName($hostName);
    }

    public function getHostNamesInHostgroup($hostGroupName): array
    {
        $hosts = Host::on($this->getDb())
            ->utilize('hostgroup')
            ->columns(['name'])
            ->filter(Filter::equal('hostgroup.name', $hostGroupName));

        $results = [];
        foreach ($hosts as $host) {
            $results[] = $host->name;
        }

        return $results;
    }

    public function getServicesByServicegroupName($serviceGroupName): array
    {
        $results = [];
        $services = Service::on($this->getDb())
            ->with(['servicegroup', 'host'])
            ->filter(Filter::equal('servicegroup.name', $serviceGroupName));

        foreach ($services as $service) {
            $results[] = [
                'host_name'           => $service->host->name,
                'service_description' => $service->name
            ];
        }

        return $results;
    }

    private function parseFilter(
        Query $query,
        array $objects,
        array $filters,
        bool $isMemberQuery = false,
        bool $isCountQuery = false,
        bool $isHostQuery = false
    ): self {
        $allFilters = [];
        foreach ($objects as $object) {
            $type = $object[0]->getType();
            $objectFilter = [];

            foreach ($filters as $filter) {
                switch ($filter['key']) {
                    case 'host_name':
                    case 'service_name':
                    case 'hostgroup_name':
                    case 'host_groups':
                    case 'servicegroup_name':
                    case 'service_groups':
                    case 'service_description':
                    case 'group_name':
                    case 'groups':
                        if ($filter['key'] !== 'service_description') {
                            $val = $object[0]->getName();
                        } else {
                            $val = $object[0]->getServiceDescription();
                        }

                        if ($filter['key'] === 'service_groups') { // Transform legacy filters
                            $filter['key'] = 'service.servicegroup.name';
                        } elseif ($filter['key'] === 'host_groups') {
                            $filter['key'] = 'host.hostgroup.name';
                        } elseif ($filter['key'] === 'service_description') {
                            $filter['key'] = 'service.name';
                        }

                        $relation = str_replace('_', '.', $filter['key']);
                        if ($filter['key'] === 'groups' || $filter['key'] === 'group_name') {
                            $relation = $object[0] instanceof NagVisServicegroup ? 'servicegroup.name' : 'hostgroup.name';
                        }

                        if ($filter['op'] === '>=') {
                            $filter['op'] = '=';
                        }

                        $objectFilter[] = $relation . $filter['op'] . $val;

                        break;
                    default:
                        throw new BackendConnectionProblem('Invalid filter key (' . $filter['key'] . ')');
                }

                if ($isMemberQuery && $object[0]->hasExcludeFilters($isCountQuery)) { // Filter excludes
                    $filter = $object[0]->getExcludeFilter($isCountQuery);
                    $parts = Str::trimSplit($filter, self::HOST_SERVICE_SEPARATOR);

                    if (! isset($parts[1]) && ($type === 'host' || ($type === 'hostgroup' && $isHostQuery))) {
                        $relation = $type === 'host' ? 'service' : 'host';
                        $objectFilter[] = "$relation.name!={$parts[0]}*";
                    } elseif ($type === 'servicegroup' || ($type === 'hostgroup' && ! $isHostQuery)) {
                        if (isset($parts[1])) {
                            // We're trying to exclude all services in a group other than the given host
                            // and service name, so the filter expression has to be like "(TRUE AND TRUE) NOT"
                            $objectFilter[] = "!(host.name={$parts[0]}*&service.name={$parts[1]}*)";
                        } else {
                            $relation = $type === 'servicegroup' ? 'service' : 'host';
                            $objectFilter[] = "$relation.name!={$parts[0]}*";
                        }
                    }
                }
            }

            $allFilters[] = implode('&', $objectFilter);
        }

        $parsedFilter = QueryString::fromString(implode('|', $allFilters))->parse();
        if ($query instanceof UnionQuery) {
            foreach ($query->getUnions() as $union) {
                $union->filter($parsedFilter);
            }
        } else {
            $query->filter($parsedFilter);
        }

        return $this;
    }

    protected function getGroupStateCounts(Query $query): array
    {
        $result = [];
        $isHostgroup = $query->getModel() instanceof HostgroupSummary;
        foreach ($query as $item) {
            $hostStates = [];
            if ($isHostgroup) {
                $hostStates = [
                    // Hosts
                    UNCHECKED   => [
                        'normal' => intval($item->hosts_pending)
                    ],
                    UP          => [
                        'normal'   => intval($item->hosts_up),
                        'stale'    => 0,
                        'downtime' => intval($item->hosts_up_downtime)
                    ],
                    DOWN        => [
                        'normal'   => intval($item->hosts_down),
                        'stale'    => 0,
                        'ack'      => intval($item->hosts_down_ack),
                        'downtime' => intval($item->hosts_down_downtime)
                    ],
                    UNREACHABLE => [
                        'normal'   => intval($item->hosts_unreachable),
                        'stale'    => 0,
                        'ack'      => intval($item->hosts_unreachable_ack),
                        'downtime' => intval($item->hosts_unreachable_downtime)
                    ]
                ];
            }

            $serviceStates = [
                PENDING  => [
                    'normal' => intval($item->services_pending)
                ],
                OK       => [
                    'normal'   => intval($item->services_ok),
                    'stale'    => 0,
                    'downtime' => intval($item->services_ok_downtime)
                ],
                WARNING  => [
                    'normal'   => intval($item->services_warning),
                    'stale'    => 0,
                    'ack'      => intval($item->services_warning_ack),
                    'downtime' => intval($item->services_warning_downtime)
                ],
                CRITICAL => [
                    'normal'   => intval($item->services_critical),
                    'stale'    => 0,
                    'ack'      => intval($item->services_critical_ack),
                    'downtime' => intval($item->services_critical_downtime)
                ],
                UNKNOWN  => [
                    'normal'   => intval($item->services_unknown),
                    'stale'    => 0,
                    'ack'      => intval($item->services_unknown_ack),
                    'downtime' => intval($item->services_unknown_downtime)
                ]
            ];

            $result[$item->name] = [
                'details' => [ALIAS => $item->display_name],
                'counts'  => $hostStates + $serviceStates
            ];
        }

        return $result;
    }
}
