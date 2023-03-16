<?php

namespace Icinga\Module\Nagvis\Model;

use Icinga\Module\Icingadb\Model\Service;
use ipl\Sql\Expression;

class ServicestateSummary extends Service
{
    public function getColumns()
    {
        return [
            'name',
            'name_ci',
            'services_pending'           => new Expression(
                'SUM(CASE WHEN service_state.soft_state = 99 THEN 1 ELSE 0 END)'
            ),
            'services_ok'                => new Expression(
                'SUM(CASE WHEN service_state.soft_state = 0 THEN 1 ELSE 0 END)'
            ),
            'services_ok_downtime'       => new Expression(
                'SUM(CASE WHEN service_state.soft_state = 0 AND service_state.in_downtime = \'y\' THEN 1 ELSE 0 END)'
            ),
            'services_warning'           => new Expression(
                'SUM(CASE WHEN service_state.soft_state = 1 AND service_state.is_handled = \'n\' THEN 1 ELSE 0 END)'
            ),
            'services_warning_downtime'  => new Expression(
                'SUM(CASE WHEN service_state.soft_state = 1 AND service_state.in_downtime = \'y\' THEN 1 ELSE 0 END)'
            ),
            'services_warning_ack'       => new Expression(
                'SUM(CASE WHEN service_state.soft_state = 1 AND service_state.is_acknowledged != \'n\' THEN 1 ELSE 0 END)'
            ),
            'services_critical'          => new Expression(
                'SUM(CASE WHEN service_state.soft_state = 2 AND service_state.is_handled = \'n\' THEN 1 ELSE 0 END)'
            ),
            'services_critical_downtime' => new Expression(
                'SUM(CASE WHEN service_state.soft_state = 2 AND service_state.in_downtime = \'y\' THEN 1 ELSE 0 END)'
            ),
            'services_critical_ack'      => new Expression(
                'SUM(CASE WHEN service_state.soft_state = 2 AND service_state.is_acknowledged != \'n\' THEN 1 ELSE 0 END)'
            ),
            'services_unknown'           => new Expression(
                'SUM(CASE WHEN service_state.soft_state = 3 AND service_state.is_handled = \'n\' THEN 1 ELSE 0 END)'
            ),
            'services_unknown_downtime'  => new Expression(
                'SUM(CASE WHEN service_state.soft_state = 3 AND service_state.in_downtime = \'y\' THEN 1 ELSE 0 END)'
            ),
            'services_unknown_ack'       => new Expression(
                'SUM(CASE WHEN service_state.soft_state = 3 AND service_state.is_acknowledged != \'n\' THEN 1 ELSE 0 END)'
            )
        ];
    }

    public function getDefaultSort()
    {
        return null;
    }

    public function getSearchColumns()
    {
        return ['name_ci', 'host.name_ci'];
    }
}
