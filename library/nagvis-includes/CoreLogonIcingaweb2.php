<?php

// SPDX-FileCopyrightText: 2015 Icinga GmbH <https://icinga.com>
// SPDX-License-Identifier: GPL-3.0-or-later

class CoreLogonIcingaweb2 extends CoreLogonModule
{
    public function check($printErr = true)
    {
        global $AUTH, $CORE;
        if ($AUTH->isAuthenticated()) {
            $AUTH->setTrustUsername(true);
            $AUTH->setLogoutPossible(false);
            return true;
        } else {
            // ??? ...
            die('not authenticated');
            // ...
            header('Location: /icingaweb');
            exit;
            // ... ???
            return false;
        }
    }
}
