<div class="well">


    <form class="form-inline" id="configurator-form" novalidate="novalidate">
        <fieldset class="galera-fieldset">
            <legend>Settings</legend>
            <div class="controls controls-row bottom-space">
                <input type="hidden" name="TOPO" id="topo" value="single">
                <input type="hidden" name="VERSION" id="version" value="3.x">
                <span class="gspan180">
                    <label for="vendor">Vendor</label> <img src="img/help_and_support.png" class="help-icon" id="vendor-info" data-original-title="" title="">
                    <select name="vendor" id="vendor" class="gspan180" data-original-title="" title="">
                        <option value="">Select</option>
                        <option value="percona">Percona XtraDb Cluster</option>
                        <option value="mariadb">MariaDB Cluster</option>
                        <option value="codership">Codership</option>
                    </select>
                </span>
                <span class="span110">
                    <label for="GaleraVersion">MySQL Version</label>
                    <select name="GaleraVersion" id="GaleraVersion" class="input-small span110"><option value="5.6" selected="">10.x</option><option value="5.5">MySQL 5.5.x</option></select>
                </span>
                <span class="span2">
                    <label class="control-label" for="cloud">Infrastructure</label> <img src="img/help_and_support.png" class="help-icon" id="infrastructure-info" data-original-title="" title="">
                    <select name="cloud" id="cloud" class="span2" data-original-title="" title="">
                        <option value="">Select</option>
                        <option value="none">on-premises</option>
                        <option value="ec2">Amazon EC2</option>
                        <option value="rackspace">Rackspace Cloud</option>
                        <option value="other">Other</option>
                    </select>
                </span>
                <span class="gspan390">
                    <label class="control-label" for="os">Operating System</label> <img src="img/help_and_support.png" class="help-icon" id="os-info" data-original-title="" title="">
                    <select name="OS" id="os" class="gspan390">
                        <option value="">Select</option>
                        <option value="precise">Ubuntu 12.04</option>
                        <option value="trusty">Ubuntu 14.04</option>
                        <option value="squeeze">Debian 6.0.x</option>
                        <option value="wheezy">Debian 7.x</option>
                        <option value="rhel6">RHEL6 - Redhat 6.x/Fedora/Centos 6.x/OLN 6.x/Amazon AMI</option>

                        <option value="rhel7">RHEL7 - Redhat/Fedora/Centos/OLN 7.x/Amazon AMI</option></select>
                </span>
                <span class="gspan180">
                    <label class="control-label" for="platform">Platform</label> <img src="img/help_and_support.png" class="help-icon" id="platform-info" data-original-title="" title="">
                    <select name="platform" id="platform" class="gspan180">
                        <option selected="" value="linux64">Linux 64-bit (x86_64)</option>
                    </select>
                </span>
                <span class="span110">
                    <label for="dbnodes"># of DB Nodes</label><img src="img/help_and_support.png" class="help-icon" id="no-server-info" data-original-title="" title="">
                    <select name="dbnodes" id="dbnodes" class="input-small span110">
                        <option value="3">3</option>
                        <option value="5">5</option>
                        <option value="7">7</option>
                        <option value="9">9</option>
                    </select>
                </span>
                <span class="gspan160">
                    <label for="cores">Number of cores</label> <img src="img/help_and_support.png" class="help-icon" id="cores-info" data-original-title="" title="">
                    <select name="cores" class="gspan160" id="cores">
                        <option value="">Select</option>
                        <option value="2">2</option>
                        <option value="4">4</option>
                        <option value="8">8</option>
                        <option value="12">12</option>
                        <option value="16">16</option>
                        <option value="24">24</option>
                        <option value="32">32</option>
                        <option value="48">48</option>
                    </select>
                </span>
                <span class="gspan160">
                    <label for="memory">Server memory</label> <img src="img/help_and_support.png" class="help-icon" id="memory-info" data-original-title="" title="">
                    <select name="memory" id="memory" class="gspan160" data-original-title="" title=""><option value="">Select</option><option value="512">512MB</option><option value="1024">1GB</option><option value="2048">2GB</option><option value="4096">4GB</option><option value="8192">8GB</option><option value="16384">16GB</option><option value="24576">24GB</option><option value="32768">32GB</option><option value="49152">48GB</option><option value="65536">64GB</option><option value="131072">128GB</option><option value="262144">256GB</option></select>
                </span>
            </div>
            <hr>
            <div class="controls controls-row bottom-space">
                <span class="gspan180">
                    <label for="innodb_buffer_pool_size">InnoDB Buffer Pool(MB)</label> <img src="img/help_and_support.png" class="help-icon" id="innodb-info" data-original-title="" title="">
                    <input maxlength="12" id="innodb_buffer_pool_size" name="innodb_buffer_pool_size" class="gspan170" data-original-title="" title="">
                </span>
                <span class="gspan160">
                    <label for="DATADIR">Data Dir</label> <img src="img/help_and_support.png" class="help-icon" id="datadir-info" data-original-title="" title="">
                    <input maxlength="255" name="DATADIR" id="DATADIR" value="/var/lib/mysql" class="gspan2" required="" data-original-title="" title="">
                </span>
                <span class="gspan160">
                    <label for="INSTALLDIR_MYSQL">Install Dir</label>
                    <input disabled="" maxlength="255" id="INSTALLDIR_MYSQL" name="INSTALLDIR_MYSQL" value="/usr/local/" class="gspan2">
                </span>
                <span class="span2">
                    <label for="connections">Max connections</label> <img src="img/help_and_support.png" class="help-icon" id="connections-info" data-original-title="" title="">
                    <input class="span2" id="connections" name="CONNECTIONS" value="200" required="" data-original-title="" title="">
                </span>
                <span class="gspan160">
                    <label for="mysql_root_pass">MySQL root password</label> <img src="img/help_and_support.png" class="help-icon" id="mysqlpassword-info" data-original-title="" title="">
                    <input class="span2" id="mysql_root_pass" name="mysql_root_pass" value="password" required="">
                </span>
            </div>
            <hr>
            <div class="controls controls-row bottom-space">
                <span class="gspan180">
                    <label for="dbsize">Database Size</label> <img src="img/help_and_support.png" class="help-icon" id="dbsize-info" data-original-title="" title="">
                    <select name="dbsize" id="dbsize" class="gspan180">
                        <option selected="" value="8">&lt; 8GB</option>
                        <option value="16">&lt; 16GB</option>
                        <option value="32">&lt; 32GB</option>
                        <option value="64">&lt; 64GB</option>
                        <option value="128">&lt; 128GB</option>
                        <option value="256">&lt; 256GB</option>
                        <option value="512">&lt; 512GB</option>
                        <option value="1024">&lt; 1TB</option>
                        <option value="2048">&lt; 2TB</option>
                        <option value="4096">&gt; 2TB</option>
                    </select>
                </span>
                <span class="span3">
                    <label for="usage">Workload</label> <img src="img/help_and_support.png" class="help-icon" id="usage-info" data-original-title="" title="">
                    <select name="usage" id="usage" class="span3">
                        <option value="0">Low write/high read</option>
                        <option selected="" value="1">Medium write/high read</option>
                        <option value="2">High write/high read</option>
                    </select>
                </span>
                <span class="span3">
                    <label for="gcache">GCache Size</label> <img src="img/help_and_support.png" class="help-icon" id="gcache-info" data-original-title="" title="">
                    <select name="GCACHE" id="gcache" class="span3">
                        <option selected="" value="128">128MB (Galera default value)</option>
                        <option value="1024">1GB</option>
                        <option value="2048">2GB</option>
                        <option value="4096">4GB</option>
                        <option value="8192">8GB</option>
                        <option value="16384">16GB</option>
                        <option value="32768">32GB</option>
                        <option value="65536">64GB</option>
                    </select>
                </span>
            </div>
            <hr>
            <div class="controls controls-row bottom-space">
                <span class="span110">
                    <label for="file_per_table">File per table</label> <img src="img/help_and_support.png" class="help-icon" id="filetable-info" data-original-title="" title="">
                    <div class="switch switch-small has-switch" id="switch-file">
                        <div class="switch-animate switch-on"><input type="checkbox" checked="" id="file_per_table" name="file_per_table"><span class="switch-left switch-small">ON</span><label class="switch-small" for="file_per_table">&nbsp;</label><span class="switch-right switch-small">OFF</span></div>
                    </div>
                </span>
                <span class="span2">
                    <label for="dns_resolve">Skip dns resolve</label> <img src="img/help_and_support.png" class="help-icon" id="dns-info" data-original-title="" title="">
                    <div class="switch switch-small has-switch" id="switch-dns">
                        <div class="switch-on switch-animate"><input type="checkbox" checked="" id="dns_resolve" name="dns_resolve"><span class="switch-left switch-small">ON</span><label class="switch-small" for="dns_resolve">&nbsp;</label><span class="switch-right switch-small">OFF</span></div>
                    </div>
                </span>
                <span class="gspan1">
                    <label for="wan">WAN</label> <img src="img/help_and_support.png" class="help-icon" id="wan-info" data-original-title="" title="">
                    <div class="switch switch-small has-switch" id="switch-wan">
                        <div class="switch-on switch-animate"><input type="checkbox" id="wan" name="wan"><span class="switch-left switch-small">ON</span><label class="switch-small" for="wan">&nbsp;</label><span class="switch-right switch-small">OFF</span></div>
                    </div>
                </span>
                <span class="gspan1">
                    <label for="firewall">Firewall</label> <img src="img/help_and_support.png" class="help-icon" id="firewall-info" data-original-title="" title="">
                    <div class="switch switch-small has-switch" id="switch-firewall">
                        <div class="switch-off switch-animate"><input type="checkbox" id="firewall" name="firewall"><span class="switch-left switch-small">ON</span><label class="switch-small" for="firewall">&nbsp;</label><span class="switch-right switch-small">OFF</span></div>
                    </div>
                </span>
                <span class="gspan1" id="span-networkless" style="display: none;">
                    <label for="networkless">No Internet</label> <img src="img/help_and_support.png" class="help-icon" id="internet-info">
                    <div class="switch switch-small has-switch" id="switch-internet" data-on-label="YES" data-off-label="NO">
                        <div class="switch-animate switch-off"><input type="checkbox" id="networkless" name="networkless"><span class="switch-left switch-small">YES</span><label class="switch-small" for="networkless">&nbsp;</label><span class="switch-right switch-small">NO</span></div>
                    </div>
                </span>
                <span class="gspan210" id="span-yum">
                    <label for="yum">APT/YUM for ClusterControl<img src="img/help_and_support.png" class="help-icon" id="yum-info" data-original-title="" title=""></label>
                    <div class="switch switch-small has-switch" id="switch-yum" data-on-label="ON" data-off-label="OFF">
                        <div class="switch-animate switch-on"><input type="checkbox" checked="" id="yum" name="yum"><span class="switch-left switch-small">ON</span><label class="switch-small" for="yum">&nbsp;</label><span class="switch-right switch-small">OFF</span></div>
                    </div>
                </span>
            </div>
            <div class="controls controls-row">
                <a href="#Change Default Port" class="pull-right" id="change_port_link">Change Default Port</a>
            </div>
            <div id="port-div" style="display: none;">
                <fieldset class="galera-fieldset">
                    <legend>Ports</legend>
                    <div class="controls controls-row">
                        <span class="gspan2">
                            <label for="PORT">MySQL server port</label> <img src="img/help_and_support.png" class="help-icon" id="mysqlport-info" data-original-title="" title="">
                            <input size="12" maxlength="12" value="3306" id="PORT" name="MYSQLPORT" class="input-small span110">
                        </span>
                        <span class="span3">
                            <label for="PORT2">Galera Group Communication</label> <img src="img/help_and_support.png" class="help-icon" id="galeraport-info" data-original-title="" title="">
                            <input disabled="" size="12" maxlength="12" value="4567" id="PORT2" name="GALERAPORT" class="input-small span110">
                        </span>
                        <span class="gspan2">
                            <label for="PORT4">Galera SST</label> <img src="img/help_and_support.png" class="help-icon" id="galerasstport-info" data-original-title="" title="">
                            <input disabled="" size="12" maxlength="12" value="4444" id="RSYNCPORT" name="RSYNCPORT" class="input-small span110">
                        </span>
                    </div>
                </fieldset>
            </div>
        </fieldset>
        <fieldset class="galera-fieldset">
            <legend>DB Nodes: Enter hostname or IP address</legend>
            <div id="dbnodes-div"><div class="controls controls-row"><span class="span120" id="db_hostnames"><label for="MASTERHOST_1">DB1 IP Address</label><input type="text" size="16" maxlength="15" name="MASTERHOST_1" id="MASTERHOST_1" class="input-small span110" required=""></span><span class="span120" id="db_hostnames"><label for="MASTERHOST_2">DB2 IP Address</label><input type="text" size="16" maxlength="15" name="MASTERHOST_2" id="MASTERHOST_2" class="input-small span110" required=""></span><span class="span120" id="db_hostnames"><label for="MASTERHOST_3">DB3 IP Address</label><input type="text" size="16" maxlength="15" name="MASTERHOST_3" id="MASTERHOST_3" class="input-small span110" required=""></span></div></div>
            <div id="segments-div"><div class="controls controls-row"><span class="span120" id=""><label for="SEGMENTID_1">gmcast.segment 1 </label><input type="text" size="16" maxlength="15" name="SEGMENTID_1" id="SEGMENTID_1" value="0" class="input-small span110" required=""></span><span class="span120" id=""><label for="SEGMENTID_2">gmcast.segment 2 </label><input type="text" size="16" maxlength="15" name="SEGMENTID_2" id="SEGMENTID_2" value="0" class="input-small span110" required=""></span><span class="span120" id=""><label for="SEGMENTID_3">gmcast.segment 3 </label><input type="text" size="16" maxlength="15" name="SEGMENTID_3" id="SEGMENTID_3" value="0" class="input-small span110" required=""></span></div></div>
            <div class="controls controls-row" style="display: none">
                <a href="#Advance Settings" class="pull-right" id="advance_settings_link">Advance Settings</a>
            </div>
        </fieldset>
        <fieldset class="galera-fieldset">
            <legend>ClusterControl Node</legend>
            <div class="controls controls-row">
                <span class="gspan1">
                    <label for="CMON_MONITOR">Host/IP Adress</label>
                    <input size="16" maxlength="15" name="CMON_MONITOR" id="CMON_MONITOR" class="gspan1">
                </span>
                <span class="gspan1">
                    <label for="CC_MEMORY">Server memory</label>
                    <select name="CC_MEMORY" id="CC_MEMORY" class="gspan1">
                        <option value="">Select</option>
                        <option value="512">512M</option>
                        <option value="1024">1GB</option>
                        <option value="2048">2GB</option>
                        <option value="4096">4GB</option>
                        <option value="8192">&gt;8GB</option>
                    </select>
                </span>
                <span class="gspan1">
                    <label for="user">SSH User</label>
                    <input size="12" maxlength="12" value="" id="user" name="USER" class="gspan1"> <img src="img/help_and_support.png" class="help-icon" id="osuser-info" data-original-title="" title="">
                </span>
                <span class="span3">
                    <label for="EC2_KEYPAIR">SSH Key File</label>
                    <input placeholder="for example: /root/.ssh/id_rsa" maxlength="255" name="EC2_KEYPAIR" id="EC2_KEYPAIR" class="span3">
                </span>
                <span class="span1">
                    <label for="ssh_port">SSH Port</label>
                    <input size="12" maxlength="12" value="22" id="ssh_port" name="ssh_port" class="span1" required="">
                </span>
                <span class="gspan170">
                    <label for="cmonpassword">CMON Agent Password</label> <img src="img/help_and_support.png" class="help-icon" id="cmonpassword-info" data-original-title="" title="">
                    <input size="12" maxlength="32" value="cmon" id="cmonpassword" name="CMONPASSWORD" class="span130" required="">
                </span>
            </div>
        </fieldset>
        <div class="controls controls-row" style="padding-top: 15px">
            <span class="span1"> <label for="email">Email</label> </span>
            <span><input value="" id="email" name="email" class="span3" required=""></span>
        </div>
        <div class="controls controls-row" style="padding-top: 15px">
            <span class="span10">
                <label class="checkbox">
                    <input type="checkbox"> Would you like to be informed about new feature and releases, critical bug fixes, and configuration updates?
                </label>
                <br><br><b>Attention: Packages may be removed during the automatic installation process. Use clean/dedicated servers.</b>
            </span>
        </div>
        <div class="controls controls-row" style="margin-top: 25px; padding-right: 10px">
            <button type="button" class="btn btn-primary pull-right" id="btn-generate">Generate</button>
        </div>
        <div id="showProgress" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
            <div class="modal-header">
                <h3 id="myModalLabel">Generate Script</h3>
            </div>
            <div class="modal-body" id="modal-content">
                Please wait.....
                <div class="progress progress-striped active">
                    <div class="bar" id="progress_bar" style="width: 0%;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary has-spinner" id="btn_generate_config" aria-hidden="true" style="display: none;"><span class="spinner"><i class="icon-spin icon-refresh"></i></span>Generate</button><button class="btn" data-dismiss="modal" aria-hidden="true" id="close_button">Close</button>
            </div>
        </div>
    </form>

</div>