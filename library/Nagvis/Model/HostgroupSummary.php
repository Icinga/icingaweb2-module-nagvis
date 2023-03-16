<?php

namespace Icinga\Module\Nagvis\Model;

use Icinga\Module\Icingadb\Model\Hostgroupsummary as BaseHostgroupSummary;
use ipl\Sql\Expression;

class HostgroupSummary extends BaseHostgroupSummary
{
    public function getColumns()
    {
        return [
            'name'                       => 'hostgroup_name',
            'display_name'               => 'hostgroup_display_name',
            // Host state summary
            'hosts_pending'              => new Expression(
                'SUM(CASE WHEN host_state = 99 THEN 1 ELSE 0 END)'
            ),
            'hosts_up'                   => new Expression(
                'SUM(CASE WHEN host_state = 0 THEN 1 ELSE 0 END)'
            ),
            'hosts_up_downtime'          => new Expression(
                'SUM(CASE WHEN host_state = 0 AND host_state_in_downtime = \'y\' THEN 1 ELSE 0 END)'
            ),
            'hosts_down'                 => new Expression(
                'SUM(CASE WHEN host_state = 1 AND host_handled = \'n\' THEN 1 ELSE 0 END)'
            ),
            'hosts_down_downtime'        => new Expression(
                'SUM(CASE WHEN host_state = 1 AND host_state_in_downtime = \'y\' THEN 1 ELSE 0 END)'
            ),
            'hosts_down_ack'             => new Expression(
                'SUM(CASE WHEN host_state = 1 AND host_state_is_acknowledged != \'n\' THEN 1 ELSE 0 END)'
            ),
            'hosts_unreachable'          => new Expression(
                'SUM(CASE WHEN host_state = 2 AND host_handled = \'n\' THEN 1 ELSE 0 END)'
            ),
            'hosts_unreachable_downtime' => new Expression(
                'SUM(CASE WHEN host_state = 2 AND host_state_in_downtime = \'y\' THEN 1 ELSE 0 END)'
            ),
            'hosts_unreachable_ack'      => new Expression(
                'SUM(CASE WHEN host_state = 2 AND host_state_is_acknowledged != \'n\' THEN 1 ELSE 0 END)'
            ),
            'hosts_unknown'              => new Expression(
                'SUM(CASE WHEN host_state = 3 AND host_handled = \'n\' THEN 1 ELSE 0 END)'
            ),
            'hosts_unknown_downtime'     => new Expression(
                'SUM(CASE WHEN host_state = 3 AND host_state_in_downtime = \'y\' THEN 1 ELSE 0 END)'
            ),
            'hosts_unknown_ack'          => new Expression(
                'SUM(CASE WHEN host_state = 3 AND host_state_is_acknowledged != \'n\' THEN 1 ELSE 0 END)'
            ),
            // Service state summary
            'services_pending'           => new Expression(
                'SUM(CASE WHEN service_state = 99 THEN 1 ELSE 0 END)'
            ),
            'services_ok'                => new Expression(
                'SUM(CASE WHEN service_state = 0 THEN 1 ELSE 0 END)'
            ),
            'services_ok_downtime'       => new Expression(
                'SUM(CASE WHEN service_state = 0 AND service_state_in_downtime = \'y\' THEN 1 ELSE 0 END)'
            ),
            'services_warning'           => new Expression(
                'SUM(CASE WHEN service_state = 1 AND service_handled = \'n\' THEN 1 ELSE 0 END)'
            ),
            'services_warning_downtime'  => new Expression(
                'SUM(CASE WHEN service_state = 1 AND service_state_in_downtime = \'y\' THEN 1 ELSE 0 END)'
            ),
            'services_warning_ack'       => new Expression(
                'SUM(CASE WHEN service_state = 1 AND service_state_is_acknowledged != \'n\' THEN 1 ELSE 0 END)'
            ),
            'services_critical'          => new Expression(
                'SUM(CASE WHEN service_state = 2 AND service_handled = \'n\' THEN 1 ELSE 0 END)'
            ),
            'services_critical_downtime' => new Expression(
                'SUM(CASE WHEN service_state = 2 AND service_state_in_downtime = \'y\' THEN 1 ELSE 0 END)'
            ),
            'services_critical_ack'      => new Expression(
                'SUM(CASE WHEN service_state = 2 AND service_state_is_acknowledged != \'n\' THEN 1 ELSE 0 END)'
            ),
            'services_unknown'           => new Expression(
                'SUM(CASE WHEN service_state = 3 AND service_handled = \'n\' THEN 1 ELSE 0 END)'
            ),
            'services_unknown_downtime'  => new Expression(
                'SUM(CASE WHEN service_state = 3 AND service_state_in_downtime = \'y\' THEN 1 ELSE 0 END)'
            ),
            'services_unknown_ack'       => new Expression(
                'SUM(CASE WHEN service_state = 3 AND service_state_is_acknowledged != \'n\' THEN 1 ELSE 0 END)'
            )
        ];
    }

    public function getUnions()
    {
        $unions = parent::getUnions();
        $unions[0][2] = array_merge(
            $unions[0][2],
            [
                'host_state_in_downtime'        => 'host.state.in_downtime',
                'host_state_is_acknowledged'    => 'host.state.is_acknowledged',
                'service_state_in_downtime'     => new Expression('NULL'),
                'service_state_is_acknowledged' => new Expression('NULL')
            ]
        );

        $unions[1][2] = array_merge(
            $unions[1][2],
            [
                'host_state_in_downtime'        => new Expression('NULL'),
                'host_state_is_acknowledged'    => new Expression('NULL'),
                'service_state_in_downtime'     => 'service.state.in_downtime',
                'service_state_is_acknowledged' => 'service.state.is_acknowledged'
            ]
        );

        $unions[2][2] = array_merge(
            $unions[2][2],
            [
                'host_state_in_downtime'        => new Expression('NULL'),
                'host_state_is_acknowledged'    => new Expression('NULL'),
                'service_state_in_downtime'     => new Expression('NULL'),
                'service_state_is_acknowledged' => new Expression('NULL')
            ]
        );

        return $unions;
    }
}
