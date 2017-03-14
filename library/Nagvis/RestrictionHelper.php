<?php

namespace Icinga\Module\Nagvis;

use Icinga\Authentication\Auth;

/**
 * NagVis restriction helper
 */
class RestrictionHelper
{
    /**
     * Get the regular expression for validating map names
     *
     * @return  string|null
     */
    public static function getRegex()
    {
        $mapFilters = array();
        foreach (Auth::getInstance()->getRestrictions('nagvis/map/filter') as $mapFilter) {
            if ($mapFilter !== '') {
                $mapFilters = array_merge($mapFilters, array_map('trim', explode(',', $mapFilter)));
            }
        }

        if (! empty($mapFilters)) {
            $mapRegexParts = array();
            foreach (array_unique($mapFilters) as $mapFilter) {
                $nonWildcards = array();
                foreach (explode('*', $mapFilter) as $nonWildcard) {
                    $nonWildcards[] = preg_quote($nonWildcard, '/');
                }
                $mapRegexParts[] = implode('.*', $nonWildcards);
            }

            return '/^(?:' . implode('|', $mapRegexParts) . ')$/i';
        }

        return null;
    }
}
