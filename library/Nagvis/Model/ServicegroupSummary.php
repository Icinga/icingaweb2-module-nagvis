<?php

namespace Icinga\Module\Nagvis\Model;

use Icinga\Module\Icingadb\Model\ServicegroupSummary as BaseServicegroupSummary;
use ipl\Sql\Expression;

class ServicegroupSummary extends BaseServicegroupSummary
{
    public function getColumns()
    {
        return [
            'display_name'               => 'servicegroup_display_name',
            'name'                       => 'servicegroup_name',
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
                'service_state_in_downtime'     => 'service.state.in_downtime',
                'service_state_is_acknowledged' => 'service.state.is_acknowledged'
            ]
        );

        $unions[1][2] = array_merge(
            $unions[1][2],
            [
                'service_state_in_downtime'     => new Expression('NULL'),
                'service_state_is_acknowledged' => new Expression('NULL')
            ]
        );

        return $unions;
    }
}
