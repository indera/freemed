<?php
 // $Id$
 // note: patient authorizations module
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 //       adam b (gdrago23@yahoo.com)
 // lic : GPL, v2

if (!defined("__AUTHORIZATIONS_MODULE_PHP__")) {

define (__AUTHORIZATIONS_MODULE_PHP__, true);

class authorizationsModule extends freemedEMRModule {

	var $MODULE_NAME    = "Insurance Authorizations";
	var $MODULE_VERSION = "0.1";
	var $MODULE_DESCRIPTION = "
		Insurance authorizations are used to track whether
		a patient is authorized by his or her insurance
		company for service during a particular period of
		time. If you do not use insurance support in
		FreeMED, this module is not needed.
	";

	var $record_name    = "Authorizations";
	var $table_name     = "authorizations";

	var $variables = array (
		"authdtmod",
		"authdtbegin",
		"authdtend",
		"authnum",
		"authtype",
		"authprov",
		"authprovid",
		"authinsco",
		"authvisits",
		"authcomment",
		"authpatient",
		"authdtadd"
	);

	function authorizationsModule () {
		$this->freemedEMRModule();
	} // end constructor authorizationsModule

	function form () {
		reset($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

     switch ($action) { // internal action switch
      case "addform":
       // do nothing
       break; // end internal addform
      case "modform":
       if (($patient<1) OR (empty($patient))) {
         echo "
           <$HEADERFONT_B>"._("You must select a patient.")."<$HEADERFONT_E>
         ";
         freemed_display_box_bottom ();
         DIE("");
       }
       $r = freemed_get_link_rec ($id, $this->table_name);
       extract ($r);
       break; // end internal modform
     } // end internal action switch

     $pnotesdt     = $cur_date;

     echo "
       <P>

       <FORM ACTION=\"$this->page_name\" METHOD=POST>
       <INPUT TYPE=HIDDEN NAME=\"action\"  VALUE=\"".
         ( ($action=="addform") ? "add" : "mod" )."\">
       <INPUT TYPE=HIDDEN NAME=\"id\"      VALUE=\"".prepare($id)."\">
       <INPUT TYPE=HIDDEN NAME=\"patient\" VALUE=\"".prepare($patient)."\">
       <INPUT TYPE=HIDDEN NAME=\"authpatient\" VALUE=\"".prepare($patient)."\">
       <INPUT TYPE=HIDDEN NAME=\"module\"  VALUE=\"".prepare($module)."\">

       <TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3
        VALIGN=MIDDLE ALIGN=CENTER>
        
       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>"._("Starting Date")." : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
         ".date_entry("authdtbegin")."
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>"._("Ending Date")." : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
         ".date_entry("authdtend")."
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>"._("Authorization Number")." : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
         <INPUT TYPE=TEXT NAME=\"authnum\" SIZE=30
          MAXLENGTH=25 VALUE=\"".prepare($authnum)."\">
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>"._("Authorization Type")." : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
         <SELECT NAME=\"authtype\">
          <OPTION VALUE=\"0\" ".
          ( ($authtype <  1) ? "SELECTED" : "" ).">"._("NONE SELECTED")."
          <OPTION VALUE=\"1\" ".
          ( ($authtype == 1) ? "SELECTED" : "" ).">physician
          <OPTION VALUE=\"2\" ".
          ( ($authtype == 2) ? "SELECTED" : "" ).">insurance company
          <OPTION VALUE=\"3\" ".
          ( ($authtype == 3) ? "SELECTED" : "" )."
           >certificate of medical neccessity
          <OPTION VALUE=\"4\" ".
          ( ($authtype == 4) ? "SELECTED" : "" ).">surgical
          <OPTION VALUE=\"5\" ".
          ( ($authtype == 5) ? "SELECTED" : "" ).">worker's compensation
          <OPTION VALUE=\"6\" ".
          ( ($authtype == 6) ? "SELECTED" : "" ).">consulation
         </SELECT>
        </TD>
       </TR>
     ";

     $phys_q="SELECT * FROM physician ORDER BY phylname,phyfname";
     $phys_r=$sql->query($phys_q);
     $ins_q="SELECT * FROM insco ORDER BY insconame,inscostate,inscocity";
     $ins_r=$sql->query($ins_q);
     
     echo "
       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>"._("Authorizing Provider")." : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
     ".
     freemed_display_selectbox ($phys_r, "#phylname#, #phyfname#", "authprov")
     ."
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>"._("Provider Identifier")." : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
         <INPUT TYPE=TEXT NAME=\"authprovid\" SIZE=20 MAXLENGTH=15
          VALUE=\"".prepare($authprovid)."\">
         </SELECT>
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>"._("Authorizing Insurance Company")." : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
     ".
     freemed_display_selectbox ($ins_r, 
       "#insconame# (#inscocity#,#inscostate#)", "authinsco")
     ."
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>"._("Number of Visits")." : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
     ".fm_number_select ("authvisits", 0, 100)."
        </TD>
       </TR>

       <TR>
        <TD ALIGN=RIGHT>
         <$STDFONT_B>"._("Comment")." : <$STDFONT_E>
        </TD>
        <TD ALIGN=LEFT>
         <INPUT TYPE=TEXT NAME=\"authcomment\" SIZE=30 MAXLENGTH=100
          VALUE=\"".prepare($authcomment)."\">
        </TD>
       </TR>
 
       </TABLE>

       <CENTER>
       <INPUT TYPE=SUBMIT VALUE=\"  ".
         ( ($action=="addform") ? _("Add") : _("Modify"))."  \">
       <INPUT TYPE=RESET  VALUE=\" "._("Clear")." \">
       </CENTER>
       </FORM>

       <CENTER>
        <A HREF=\"$this->page_name?$_auth&module=$module&patient=$patient\"
         ><$STDFONT_B>". 
	  ( ($action=="addform") ? _("Abandon Addition") :
	    _("Abandon Modification") )."<$STDFONT_E></A>
       </CENTER>
     ";
	} // end function authorizationsModule->form()

	function add () {
		global $authpatient, $authdtbegin, $authdtend, $authdtadd, $cur_date, $patient;
		$authdtbegin = fm_date_assemble("authdtbegin");
		$authdtend   = fm_date_assemble("authdtend");
		$authdtadd   = $cur_date;
		$authpatient = $patient;
		$this->_add();
	} // end function authorizationsModule->add()

	function mod () {
		global $authpatient, $authdtbegin, $authdtend, $authdtmod, $cur_date, $patient;
		$authdtbegin = fm_date_assemble("authdtbegin");
		$authdtend   = fm_date_assemble("authdtend");
		$authdtmod    = $cur_date;
		$authpatient = $patient;
		$this->_mod();
	} // end function authorizationsModule->mod()

	function view () {
		reset ($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;

     $query = "SELECT * FROM $this->table_name
        WHERE (authpatient='".addslashes($patient)."')
        ORDER BY authdtbegin,authdtend";
     $result = $sql->query ($query);
     $rows = ( ($result > 0) ? $sql->num_rows ($result) : 0 );

     if ($rows < 1) {
       echo "
         <P>
         <CENTER>
         <$STDFONT_B>"._("This patient has no authorizations.")."<$STDFONT_E>
         </CENTER>
         <P>
         <CENTER>
         <A HREF=\"$this->page_name?$_auth&action=addform&module=$module&patient=$patient\"
          ><$STDFONT_B>"._("Add")." "._("$record_name")."<$STDFONT_E></A>
         <B>|</B>
         <A HREF=\"manage.php?$_auth&id=$patient\"
          ><$STDFONT_B>"._("Manage Patient")."<$STDFONT_E></A>
         </CENTER>
         <P>
       ";
       freemed_display_box_bottom ();
       freemed_close_db ();
       freemed_display_html_bottom ();
       DIE("");
     } // if there are none...

       // or else, display them...
     echo "
       <P>
     ".
     freemed_display_itemlist (
       $result,
       $this->page_name,
       array (
         "Dates" => "authdtbegin",
	 "<FONT COLOR=\"#000000\">_</FONT>" => 
	    "", // &nbsp; doesn't work, dunno why
	 "&nbsp;"  => "authdtend"
       ),
       array ("", "/", "")
     );
	} // end function authorizationsModule->view()

} // end class authorizationsModule

register_module ("authorizationsModule");

} // end if defined

?>
