echo "xdebug.remote_enable=Off" >> /etc/php5/mods-available/xdebug.ini
echo "xdebug.remote_host="localhost"" >> /etc/php5/mods-available/xdebug.ini
echo "xdebug.remote_port=9001" >> /etc/php5/mods-available/xdebug.ini
echo "xdebug.remote_handler="dbgp" " >> /etc/php5/mods-available/xdebug.ini
echo "xdebug.cli_color=2" >> /etc/php5/mods-available/xdebug.ini
echo "xdebug.max_nesting_level=1000" >> /etc/php5/mods-available/xdebug.ini
echo "xdebug.collect_params=2" >> /etc/php5/mods-available/xdebug.ini
echo "xdebug.collect_return=On" >> /etc/php5/mods-available/xdebug.ini
