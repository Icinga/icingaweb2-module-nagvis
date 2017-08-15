# Module-Installation - Icingaweb2 module nagvis installation


## debian installation

Sorry actual no debian packages available.

## Source from github via git

Go to the icingaweb2 module directory `<ICINGAWEB_DIR>/modules` and clone the head:

     git clone https://github.com/Icinga/icingaweb2-module-nagvis.git nagvis

E.g.

	cd /usr/share/icingaweb2/modules
	git clone https://github.com/Icinga/icingaweb2-module-nagvis.git nagvis



## Enable via web

On the Web-Gui, go to Configuration->Modules and click `nagvis` and change state to `enable`.

## Or enable via icingacli

    icingacli module enable nagvis



   


