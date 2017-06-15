<?php
/*
* Copyright (C) 2016 OpenSIPS Project
*
* This file is part of opensips-cp, a free Web Control Panel Application for
* OpenSIPS SIP server.
*
* opensips-cp is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* opensips-cp is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

require("../../../../config/tools/system/clusterer/local.inc.php");
require("../../../common/mi_comm.php");
require("../../../common/cfg_comm.php");

$command="clusterer_list";

$mi_connectors=get_proxys_by_assoc_id($talk_to_this_assoc_id);
$message=mi_command($command, $mi_connectors[0], $mi_type, $errors, $status);
if($errors)
	die('Failed to query $mi_connectors[0]');

$cid="";
$sid="";
$attr="";

if ($mi_type=='json') {
	$message=json_decode($message,TRUE);
	for($c=0;$c<count($message['Cluster']);$c++) {
		$cid = $message['Cluster'][$c]['value'];
		$nodes=$message['Cluster'][$c]['children']['Node'];
		for ($s=0;$s<count($nodes);$s++) {
			if ($nodes[$s]['attributes']['DB_ID']==$_GET['id']) {
				$sid = $nodes[$s]['value'];
				$attr = $nodes[$s]['attributes'];
				break;
			}
		}	
	}
} else {
	$lines = explode("\n",$message);
	for($i=0 ; $i<count($lines) ; $i++) {
		if (preg_match('/Cluster\:\:\s+([0-9]+)/',$lines[$i],$matches)) {
			$cid = $matches[1];
		} else 
		if (preg_match('/\s+Node\:\:\s+(?P<sid>\d+)\s+DB_ID=(?P<DB_ID>\d+)\s+URL=(?P<URL>[a-zA-Z0-9\.\:]+)\s+Enabled=(?P<Enabled>\d+)\s+Link_state=(?P<Link_state>\w+)\s+Next_hop=(?P<Next_hop>\w+)\s+Description=(?P<Description>.+)/',$lines[$i],$attr)) {
			if ($attr['DB_ID']==$_GET['id']) {
				$sid = $attr['sid'];
				break;
			}
		}
	}
}
?>
	
<html>

<head>
 <title>Node Details: #<?=$_GET['id']?></title>
 <link href="style/style.css" type="text/css" rel="StyleSheet">
</head>

<body bgcolor="#e9ecef">
<center>

<?php
if ($sid=="") {
	echo("<font color='red'><b>Your OpenSIPS has no in-memory <br>info about node ".$_GET['id']."</br></font>");
} else {	
	?>
	<table width="90%" cellpadding="5" cellspacing="5" border="0" align="center">
 		<tr><td class="rowOdd"><b>Cluster ID</b></td><td><?php print "$cid"?></td></tr>
 		<tr><td class="rowEven"><b>Node ID</b></td><td><?php print "$sid"?></td></tr>
 		<tr><td class="rowOdd"><b>BIN URL</b></td><td><?php print($attr['URL'])?></td></tr>
 		<tr><td class="rowEven"><b>Enabled</b></td><td><?php print($attr['Enabled'])?></td></tr>
 		<tr><td class="rowOdd"><b>Link state</b></td><td><?php print($attr['Link_state'])?></td></tr>
 		<tr><td class="rowEven"><b>Next hop</b></td><td><?php print($attr['Next_hop'])?></td></tr>
 		<tr><td class="rowOdd"><b>Description</b></td><td><?php print($attr['Description'])?></td></tr>
	</table>

<? } ?>
</body>

</html>
