<?php
 // $Id$
 // $Author$
 // note: template for patient management functions
 // lic : GPL, v2

//----- Pull configuration for this user
if (!is_object($this_user)) $this_user = new User;

//----- Extract all configuration data
if (is_array($this_user->manage_config)) extract($this_user->manage_config);

//----- Check for a *reasonable* refresh time and summary items
if ($automatic_refresh_time > 14) $automatic_refresh = $automatic_refresh_time;
if ($num_summary_items < 1) $num_summary_items = 5;

//----- Display patient information box...
$display_buffer .= freemed_patient_box($this_patient);

//----- Suck in management panels
//-- Static first...
foreach ($static_components AS $garbage => $component) {
	switch ($component) {
		case "appointments": // Appointments static component
		include_once("lib/calendar-functions.php");
		// Add header and strip at top
		$modules[_("Appointments")] = "appointments";
		$panel[_("Appointments")] .= "
			<TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0
			 CELLPADDING=3 CLASS=\"thinbox\">
			<TR><TD COLSPAN=3 VALIGN=MIDDLE ALIGN=CENTER
			 CLASS=\"menubar_items\">
			<A HREF=\"book_appointment.php?patient=$id&type=pat\"
			>"._("Add")."</A> |
			<A HREF=\"manage_appointments.php?patient=$id\"
			>"._("View/Manage")."</A> |
			<A HREF=\"show_appointments.php?patient=$id&type=pat\"
			>"._("Show Today")."</A>
			</TD></TR>
		";

		// Get last few appointments
		$query =
			"SELECT * FROM scheduler WHERE ".
			"calpatient='".addslashes($id)."' AND ".
			"caltype='pat' AND ".
			"( caldateof > '".date("Y-m-d")."' OR ".
			  "( caldateof = '".date("Y-m-d")."' AND ".
			  "  calhour >= '".date("H")."' )".
			") LIMIT ".$num_summary_items;
		if ($debug) print "query = $query<BR>\n";
		$appoint_result = $sql->query($query);
		if (!$sql->results($appoint_result)) {
			$panel[_("Appointments")] .= "
			<TR><TD COLSPAN=3 VALIGN=MIDDLE ALIGN=CENTER>
			<B>"._("NONE")."</B>
			</TD></TR>
			";
		} else {
			$panel[_("Appointments")] .= "
			<TR><TD COLSPAN=3 VALIGN=MIDDLE ALIGN=CENTER>
			<TABLE WIDTH=\"100%\" CELLSPACING=0 CELLPADDING=0
			 BORDER=0 CLASS=\"thinbox\"><TR>
			<TD VALIGN=MIDDLE ALIGN=LEFT CLASS=\"menubar_info\">
				<B>"._("Date")."</B>
			</TD><TD VALIGN=MIDDLE ALIGN=LEFT
			 CLASS=\"menubar_info\">
				<B>"._("Time")."</B>
			</TD><TD VALIGN=MIDDLE CLASS=\"menubar_info\">
				<!-- <B>"._("Room")."</B> -->
			</TD><TD VALIGN=MIDDLE CLASS=\"menubar_info\">
				<B>"._("Description")."</B>
			</TD></TR>
			";
			while ($appoint_r=$sql->fetch_array($appoint_result)) {
				$panel[_("Appointments")] .= "
				<TR>
				<TD VALIGN=MIDDLE ALIGN=LEFT>
				".prepare(fm_date_print(
					$appoint_r["caldateof"]
				))."
				</TD><TD VALIGN=MIDDLE ALIGN=LEFT>
				".prepare(fc_get_time_string(
					$appoint_r["calhour"]
				))."
				</TD><TD VALIGN=MIDDLE ALIGN=LEFT>
				</TD><TD VALIGN=MIDDLE ALIGN=LEFT>
				".prepare($appoint_r["calprenote"])."
				</TD></TR>
				";
			} // end of looping through results
			// Show last few appointments
			$panel[_("Appointments")] .= "
			</TABLE>
			</TD></TR>
			";
		} // end of checking for results
		

		// Footer
		$panel[_("Appointments")] .= "
			</TABLE>
		";
		break; // end appointments

		case "custom_reports":
		$f_results = $sql->query("SELECT * FROM patrectemplate ".
			"ORDER BY prtname");
		$modules[_("Custom Records")] = "custom_reports";
		if ($sql->results($f_results)) {
			$panel[_("Custom Records")] .= "
			<TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0
			 CELLPADDING=3 CLASS=\"thinbox\">
			<TR><TD COLSPAN=3 VALIGN=MIDDLE ALIGN=CENTER
			 CLASS=\"menubar_items\">
			</TD></TR>
			<TR><TD ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
			<DIV ALIGN=\"CENTER\">
          		<FORM ACTION=\"custom_records.php\" METHOD=POST>
        		<INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($id)."\">
			<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"addform\">
			<SELECT NAME=\"form\">
			";
			while ($f_r = $sql->fetch_array ($f_results)) 
			$panel[_("Custom Records")] .= "<OPTION VALUE=\"".$f_r["id"]."\">".
				$f_r["prtname"]."\n"; 
			$panel[_("Custom Records")] .= "
				</SELECT>
				<INPUT TYPE=SUBMIT VALUE=\""._("Add")."\">
				</FORM>
				</DIV>
				</TD></TR></TABLE>
			";
		} else {
			// Quick null panel
			$panel[_("Custom Records")] .= "
				<TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0
				 CELLPADDING=3 CLASS=\"thinbox\">
				<TR><TD VALIGN=MIDDLE ALIGN=CENTER
				 CLASS=\"menubar_items\">
				<A HREF=\"custom_records.php?patient=$id\" 
				>"._("View/Manage")."</A>
				</TD></TR>
				<TR><TD ALIGN=CENTER VALIGN=MIDDLE>
				<B>"._("NONE")."</B>
				</TD></TR></TABLE>
			";
		} // end checking for results
		break; // end custom_reports

		case "medical_information":
		$modules[_("Medical Information")] = "medical_information";
		$panel[_("Medical Information")] = "
		<TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0
		 CELLPADDING=3 CLASS=\"thinbox\">
		<TR><TD VALIGN=MIDDLE ALIGN=CENTER
		 CLASS=\"menubar_items\">
		(no actions)
		</TD></TR>
		<TR><TD ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
		<DIV ALIGN=\"CENTER\">
		<TABLE WIDTH=\"100%\" BORDER=\"0\">
		<TR><TD ALIGN=\"LEFT\"><B>"._("Blood Type")."</B></TD> 
		<TD ALIGN=\"RIGHT\">".prepare($this_patient->local_record['ptblood'])."</TD></TR>
		</TABLE>
		</TD></TR></TABLE>";
		break; // end medical_information

		case "messages":
		$modules[_("Messages")] = "messages";
		$panel[_("Messages")] = "
		<TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0
		 CELLPADDING=3 CLASS=\"thinbox\">
		<TR><TD VALIGN=MIDDLE ALIGN=CENTER
		 CLASS=\"menubar_items\">
		<A HREF=\"messages.php?action=addform\">"._("Add")."</A>
		</TD></TR>
		<TR><TD ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
		<DIV ALIGN=\"CENTER\">
		<TABLE WIDTH=\"100%\" BORDER=\"0\">
		";
		$my_result = $sql->query("SELECT * FROM messages WHERE ".
			"msgpatient='".urlencode($id)."' ".
			"ORDER BY msgtime DESC ".
			"LIMIT ".$num_summary_items);
		if ($sql->results($my_result)) {
			$panel[_("Messages")] .= "<TR CLASS=\"menubar_info\">".
				"<TD>"._("Date")."</TD>".
				"<TD>"._("Time")."</TD>".
				"<TD>"._("Physician")."</TD>".
				"<TD>"._("Action")."</TD>".
				"</TR>\n";
			while ($my_r = $sql->fetch_array($my_result)) {
				// Transformations for date and time
				$y = $m = $d = $hour = $min = '';
				$y = substr($my_r[msgtime], 0, 4);
				$m = substr($my_r[msgtime], 4, 2);
				$d = substr($my_r[msgtime], 6, 2);
				$hour = substr($my_r[msgtime], 8, 2);
				$min  = substr($my_r[msgtime], 10, 2);

				// Get Physician object
				$this_physician = new Physician ($my_r[msgfor]);

				// Form the panel
				$panel[_("Messages")] .= "<TR>".
					"<TD ALIGN=\"LEFT\">$y-$m-$d</TD>".
					"<TD ALIGN=\"LEFT\">".fc_get_time_string($hour,$min)."</TD>".
					"<TD ALIGN=\"LEFT\"><SMALL>".$this_physician->fullName()."</SMALL></TD>".
					"<TD ALIGN=\"LEFT\">".
					template::summary_delete_link(NULL,
					"messages.php?action=del&id=".$my_r[id].
					"&return=manage").
					"</TR>\n".
					"<TR><TD COLSPAN=4 CLASS=\"infobox\"><SMALL>".
					prepare($my_r[msgtext]).
					"</SMALL></TD></TR>\n";			
			}
		} else {
			// If there are no messages regarding this patient
			$panel[_("Messages")] .= "<TR><TD ALIGN=\"CENTER\">".
			_("There are currently no messages.").
			"</TD></TR>\n";
		}
		$panel[_("Messages")] .= "
		</TABLE>
		</TD></TR></TABLE>";
		break; // end medical_information

		case "photo_id":
		// If there is a file with that name, show it, else box
		if (file_exists("img/store/$id.identification.djvu")) {
			$modules[_("Photo ID")] = "photo_id";
			$panel[_("Photo ID")] = "
			<TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0
			 CELLPADDING=3 CLASS=\"thinbox\">
			<TR><TD VALIGN=MIDDLE ALIGN=CENTER
			 CLASS=\"menubar_items\">
			<A HREF=\"photo_id.php?patient=".urlencode($id)."&".
			"return=manage\"
			 >"._("Update")."</A> |
			<A HREF=\"photo_id.php?patient=".urlencode($id)."&".
			"action=remove&return=manage\"
			 >"._("Remove")."</A>
			</TD></TR>
			<TR><TD ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
			<DIV ALIGN=\"CENTER\">
			<A HREF=\"patient_image_handler.php?".
			"patient=".urlencode($patient)."&".
			"id=identification\" TARGET=\"new\"
			onMouseOver=\"window.status='"._("Enlarge image")."'; return true;\"
			onMouseOut=\"window.status=''; return true;\"
			><EMBED SRC=\"patient_image_handler.php?".
			"patient=".urlencode($id)."&id=identification\"
			 BORDER=\"0\" ALT=\"Photographic Identification\"
			 WIDTH=\"200\" HEIGHT=\"150\"
			 TYPE=\"image/x.djvu\"
			 PLUGINSPAGE=\"".COMPLETE_URL."support/\"
			 ></EMBED></A>
			</DIV>
			</TD></TR>
			</TABLE>
			";

		} else {
			$panel[_("Photo ID")] = "
			<TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0
			 CELLPADDING=3 CLASS=\"thinbox\">
			<TR><TD VALIGN=MIDDLE ALIGN=CENTER
			 CLASS=\"menubar_items\">
			<A HREF=\"photo_id.php?patient=".urlencode($id)."\"
			 >"._("Update")."</A>
			</TD></TR>
			<TR><TD ALIGN=\"CENTER\" VALIGN=\"MIDDLE\">
			<DIV ALIGN=\"CENTER\">
			"._("No photographic identification on file.")."
			<BR><BR>
			</DIV>
			</TD></TR>
			</TABLE>
			";
		}
		break; // end photo_id

		case "patient_information":
		//----- Determine date of last visit
		$dolv_result = $sql->query(
			"SELECT * FROM scheduler WHERE ".
			"id='".addslashes($id)."' AND ".
			"(caldateof < '".date("Y-m-d")."' OR ".
			"(caldateof = '".date("Y-m-d")."' AND ".
			"calhour < '".date("H")."'))".
			"ORDER BY caldateof DESC, calhour DESC"
		);
		if (!$sql->results($dolv_result)) {
			$dolv = _("NONE");
		} else {
			$dolv_r = $sql->fetch_array($dolv_result);
			$dolv = prepare(fm_date_print($dolv_r["caldateof"]));
		} // end if there is no result
		//----- Create the panel
		$modules[_("Patient Information")] = "patient_information";
		$panel[_("Patient Information")] .= "
			<TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0
			 CELLPADDING=3 CLASS=\"thinbox\">
			<TR><TD VALIGN=MIDDLE ALIGN=CENTER
			 CLASS=\"menubar_items\" COLSPAN=2>
			<A HREF=\"patient.php?action=modform&id=$id\" 
			>"._("Modify")."</A>
			</TD></TR>
			<TR><TD ALIGN=RIGHT VALIGN=MIDDLE WIDTH=\"50%\">
				<B>"._("Date of Last Visit")."</B> :
			</TD><TD ALIGN=LEFT VALIGN=MIDDLE WIDTH=\"50%\">
				".$dolv."
			</TR><TR><TD ALIGN=RIGHT VALIGN=MIDDLE WIDTH=\"50%\">
				<B>"._("Phone Number")."</B> :
			</TD><TD ALIGN=LEFT VALIGN=MIDDLE WIDTH=\"50%\">
				".$this_patient->local_record["pthphone"]."
			</TD></TR></TABLE>
		";
		break; // end patient information

		default: // Everything else.... do nothing (ERROR)
		break; // end default
	} // end component switch
} // end static components

//-- ... then modular
foreach ($modular_components AS $garbage => $component) {
	// Determine if the class exists
	if (!is_object($module_list))
		$module_list = new module_list (PACKAGENAME, ".emr.module.php");
	
	// End checking for component
	if ($module_list->check_for($component)) {
		// Execute proper portion and add to panel
		$modules[_($module_list->get_module_name($component))] =
			$component;
		$panel[_($module_list->get_module_name($component))] .= "
			<TABLE WIDTH=\"100%\" BORDER=0 CELLSPACING=0
			 CELLPADDING=3 CLASS=\"thinbox\">
			<TR><TD VALIGN=MIDDLE ALIGN=CENTER
			 CLASS=\"menubar_items\">
			<A HREF=\"module_loader.php?module=".
			$component."&patient=$id\" 
			>"._("View/Manage")."</A> |
			<A HREF=\"module_loader.php?module=".
			$component."&patient=$id&action=addform\" 
			>"._("Add")."</A>
			</TD></TR>
			<TR><TD ALIGN=CENTER VALIGN=MIDDLE>
			".module_function($component, "summary",
				array (
					$id, // patient ID
					$num_summary_items // items per panel
				)
			)."</TD></TR></TABLE>
		";
	} else {
		// Don't do anything if it doesn't exist
	} // end checking for component existing
} // end static components

//----- Determine column requirements
if ($display_columns < 1) $display_columns = 1;
if (count($panel) > 0) {
	$column_cutoff = ceil ( count($panel) / $display_columns );
} // check for ability to display panels

//----- Display tables

if (count($panel) > 0) {
	// Sort by panel names
	ksort($panel);

	// Table header
	$display_buffer .= "
	<TABLE WIDTH=\"100%\" CELLSPACING=3 CELLPADDING=0 BORDER=0>
	<TR VALIGN=MIDDLE ALIGN=CENTER>
	";

	$column = 1; reset ($panel);
	foreach ($panel AS $k => $v) {
		// Check to see if we're on a new row yet
		if ($column > $display_columns) {
			$column = 1;

			// Display footer and new header
			$display_buffer .= "
			</TR><TR VALIGN=MIDDLE ALIGN=CENTER>
			";
		}

		// Add panel
		$myk = str_replace(" ", "_", $k);
		$display_buffer .= "
		<TD VALIGN=\"TOP\" ALIGN=\"CENTER\" WIDTH=\"".
			( (int) (100 / $display_columns) )."%\">
		<TABLE WIDTH=\"100%\" BORDER=\"0\" CELLSPACING=\"0\"
		 CELLPADDING=\"0\">
		<TR><TD CLASS=\"reverse\" VALIGN=\"MIDDLE\" ALIGN=\"CENTER\">
		<B>".prepare($k)."</B>
		</TD><TD CLASS=\"reverse\" VALIGN=\"MIDDLE\" ALIGN=\"RIGHT\">
		<A HREF=\"manage.php?id=".urlencode($id)."&".
		"action=remove&module=".urlencode($modules[$k])."\"
		onMouseOver=\"document.images.".$myk."_close.src='lib/template/default/img/close_x_pressed.png'; return true;\"
		onMouseOut=\"document.images.".$myk."_close.src='lib/template/default/img/close_x.png'; return true;\"
		><IMG NAME=\"".$myk."_close\"
		SRC=\"lib/template/default/img/close_x.png\"
		BORDER=\"0\" ALT=\"X\"></A></TD></TR>
		<TR><TD VALIGN=\"MIDDLE\" ALIGN=\"CENTER\" COLSPAN=\"2\">
		<CENTER>$v</CENTER>
		</TD></TR></TABLE>
		</TD>
		";

		// Move to the next column
		$column += 1;
	} // end looping

	// Fill up empty space
	if ($column < $display_columns) {
		for ($i=1; $i<=($display_columns-$column); $i++)
			$display_buffer .= "<TD>&nbsp;</TD>\n";
	} // end filling up empty space

	// Table footer
	$display_buffer .= "
	</TR></TABLE>
	";

} // end checking for *any* panels

// **************************************************** STATIC MODULES

//      $display_buffer .= "
//        <TR><TD ALIGN=RIGHT>
//         <B>Dependent Information</B> : 
//        </TD><TD ALIGN=LEFT>
//     ";
//      removed as part of coverage overhaul
//     if (!$this_patient->isDependent()) {
//      $dep_query = "SELECT COUNT(*) FROM patient WHERE ptdep='".
//                   $this_patient->id."'";
//      $dep_result = $sql->query($dep_query);
//      $dep_r = $sql->fetch_array($dep_result);
//      $num_deps = $dep_r[0];
//      if ($num_deps<1)
//        $display_buffer .= "No Dependents";
//      else
//        $display_buffer .= "
//	 <A HREF=\"patient.php?action=find&criteria=".
//	 "dependants&f1=$id\">"._("Dependents")."</A> [$num_deps]
//        ";
//      } else {
//      $guarantor = new Patient ($this_patient->ptdep);
//      $display_buffer .= "
//         <A HREF=\"manage.php?action=view&id=".$this_patient->ptdep."\"
//         >"._("Guarantor")."</A>
//	</TD><TD>[".$guarantor->fullName()."]</TD></TR>
//     ";
//    }

// Add configure to the menu bar
if ($action != "config") {
	$menu_bar[_("Configure")] = "manage.php?id=$id&action=config";
}


//----- Add to menu bar
$module_list = new module_list (PACKAGENAME, ".emr.module.php");
// Form template for menubar
$menu_bar = array_merge (
	$menu_bar,
	$module_list->generate_array(
		"Electronic Medical Record",
		0,
		"#name#",
		"module_loader.php?module=#class#&patient=$id"
	)
);

/*
$display_buffer .= "
	<TR><TD ALIGN=RIGHT>
	<BR>
    	<B>"._("Certifications")."</B>
	<BR>
    	</TD>
";
$category = "EMR Certifications";
$module_template = "
        <TR><TD ALIGN=RIGHT>
        <B>#name#</B> : 
        </TD><TD> 
        <A HREF=\"module_loader.php?module=#class#&action=addform&patient=$id\"
         >"._("Add")."</A>
        </TD><TD> 
        <A HREF=\"module_loader.php?module=#class#&patient=$id\"
         >"._("View/Manage")."</A>
        </TD><TD>
        </TD></TR>

";

$category = "Electronic Medical Record Report";
$module_template = "
        <TR><TD ALIGN=RIGHT>
        <B>#name#</B> : 
        </TD>
		<TD> 
        <A HREF=\"module_loader.php?module=#class#&patient=$id\"
         >"._("View")."</A>
        </TD><TD>
        </TD></TR>

";
*/

/*
$display_buffer .= "
        <!--

	  // this is commented out until we can make it work properly

        <TR><TD ALIGN=RIGHT>
        <B>"._("Reports and Certificates")."</B> : 
        </TD><TD>
        <A HREF=\"simplerep.php?action=choose&patient=$id\"
        >"._("Choose")."</A>
        </TD><TD>
        </TD><TD>
        </TD></TR>
        -->

		<!--	
        <TR><TD ALIGN=RIGHT>
        <B>"._("Patient Reports")."</B> : 
        </TD><TD>
        <A HREF=\"emrreports.php?action=choose&patient=$id\"
        >"._("Choose")."</A>
        </TD><TD>
        </TD><TD>
        </TD></TR>
        -->
        </TABLE>

        <CENTER>
		<P>
        <A HREF=\"patient.php\"
         >"._("Select Another Patient")."</A> |
	<A HREF=\"manage.php?id=$id&action=config\"
	 >"._("Configuration")."</A>
        </CENTER>
        <P>
      </CENTER>
";
*/
    
?>
