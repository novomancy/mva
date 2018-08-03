<?php
  ini_set('display_errors', 1);
  error_reporting(E_ALL ^ E_NOTICE);
  require_once('classes/DNDUser.class.php');

  //Get data from LDAP
  $error = '';
  try{
    $user = new DNDUser($_SERVER['REMOTE_USER']);
    $user->do_lookup();
  } catch (Exception $e){
    $error = $e;
  }
  
  //Add constructed attributes and humanize LDAP names
  if($error == ''){
    $user->humanize();

    $user->set_attribute('volname', substr($user->get_attribute('lastname'), 0, 14) . substr($user->get_attribute('firstname'), 0, 1));
    // if ( $user->get_attribute('affiliation') == 'Faculty' || 
    //      $user->get_attribute('affiliation') == 'Staff' ) {
    //   $user->set_attribute('pi_name', $user->get_attribute('name'));
    // } else {
    //   $user->set_attribute('pi_name', '');
    // }
  }
  // print_r($user->attributes);
?>
<!doctype html>
<html class="no-js" lang="">
  
<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>DartFS Storage Request</title>
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link rel="manifest" href="site.webmanifest">
  <link rel="apple-touch-icon" href="icon.png">
  <link rel="shortcut icon" href="favicon.ico" type="image/vnd.microsoft.icon" />
  
	<link rel="stylesheet" href="css/jquery.mobile.icons.min.css" />
	<link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile.structure-1.4.5.min.css" />
  <link rel="stylesheet" href="css/dartmouth.css" />
  <script src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
  <script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
  <script src="js/main.js"></script>
</head>

<body>
  <!--[if lte IE 9]>
    <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
  <![endif]-->
  <div class="container">
    <header>
      <a href="http://rc.dartmouth.edu/index.php/dartfs/"><img src="images/dartfs_logo.svg"></a>
      <h1>DartFS Storage Request</h1>
    </header>
    <?php if($error != ''){ ?>
    <section>
      <p>
        Error getting user information from LDAP: <?php echo $error; ?><br>
        Please contact us at <a href="mailto:research.computing@dartmouth.edu">research.computing@dartmouth.edu</a> to set up your DartFS storage.
      </p>
    </section>      
    <?php } ?>
    <section>
      <p><a href="http://rc.dartmouth.edu/index.php/dartfs/" target="_new"><strong>DartFS</strong></a> is a <a href="http://rc.dartmouth.edu/" target="_new">Research Information, Technology, and Consulting</a> service providing secure network storage to the Dartmouth community. To request DartFS storage, please complete the questions below.</p>
    </section>
    <form action="handler.php" method="post" data-ajax="false">
      <section id="page1">
        <h2>What kind of DartFS storage do you need?</h2>
        <fieldset>
          <!-- <input type="radio" name="accountType" value="faculty" id="accountFaculty">
          <label for="accountFaculty">Free Faculty DartFS</label>
          <p>The standard Faculty DartFS includes 50GB of network storage on high-performance drives and uses RITC's typical backup schedule. <a href="#helpTextBackups" class="helpBackups" data-role="button" data-icon="info" data-iconpos="notext" data-rel="popup" data-transition="pop" data-mini="true" data-inline="true"></a></p> -->
          <input type="radio" name="accountType" value="lab" id="accountLab">
          <label for="accountLab">Faculty Bill of Rights Lab DartFS</label>
          <p>The standard Lab DartFS available as part of the <a href="http://rc.dartmouth.edu/index.php/rcbor/" target="_new">Faculty Bill of Rights</a> includes 1TB of network storage on high-performance drives and uses RITC's typical backup schedule. <a href="#helpTextBackups" class="helpBackups" data-role="button" data-icon="info" data-iconpos="notext" data-rel="popup" data-transition="pop" data-mini="true" data-inline="true"></a><br>Files can be accessed by other members of a research group based on granular permissions.</p>
          <input type="radio" name="accountType" value="custom" id="accountCustom">
          <label for="accountCustom">Custom DartFS</label>
          <p>Choose your own storage quantity, speed, and backup schedule. Additional costs may apply.</p>
          <input type="radio" name="accountType" value="contact" id="accountContact" checked>
          <label for="accountContact">I'm not sure what I need</label>
          <p>RITC staff contact you to help you determine how DartFS can best work for you.</p>
        </fieldset>
      </section>  
      <section id="page2" class="hidden">
        <div id="genericData">
          <fieldset class="ids">
            <div class="ui-field-contain">
              <label for="fullname">Full name</label> <input data-mini="true" type="text" name="fullname" value="<?php echo $user->get_attribute('name'); ?>" readonly>
            </div>
            <div class="ui-field-contain">
              <label for="dept">Class or dept</label> <input data-mini="true" type="text" name="dept" value="<?php echo $user->get_attribute('deptclass'); ?>" readonly>
            </div>
            <div class="ui-field-contain">
              <label for="email">Email</label> <input data-mini="true" type="text" name="email" value="<?php echo $user->get_attribute('email'); ?>" readonly>
            </div>
            <div class="ui-field-contain">
              <label for="netid">NetID</label> <input data-mini="true" type="text" name="netid" value="<?php echo $user->get_attribute('netid'); ?>" readonly>
            </div>
            <!-- <div class="ui-field-contain">
              <label for="pi">Advisor/PI's Name</label><input data-mini="true" type="text" value="<?php //echo $user->get_attribute('pi_name'); ?>" name="pi">
            </div> -->
          </fieldset>
        </div>
        <hr>
        <!-- <div id="describeFaculty" class="hidden">
          <p>Submit a DartFS faculty request that includes:</p>
          <ul>
            <li>50GB free storage.</li>
            <li>High-speed disks.</li>
            <li>Standard backup schedule typical backup schedule. <a href="#helpTextBackups" class="helpBackups" data-role="button" data-icon="info" data-iconpos="notext" data-rel="popup" data-transition="pop" data-mini="true" data-inline="true"></a></li>
          </ul>
        </div> -->
        <div id="describeLab" class="hidden">
          <p>Submit a DartFS lab storage request that includes:</p>
          <ul>
            <li>1TB free storage. <a href="#helpTextPerformance" class="helpPerformance" data-role="button" data-icon="info" data-iconpos="notext" data-rel="popup" data-transition="pop" data-mini="true" data-inline="true"></a></li>
            <li>High-speed disks.</li>
            <li>Standard backup schedule. <a href="#helpTextBackups" class="helpBackups" data-role="button" data-icon="info" data-iconpos="notext" data-rel="popup" data-transition="pop" data-mini="true" data-inline="true"></a></li>
            <li>Access by multiple NetIDs. We will contact you to set up permissions.</li>
          </ul>
        </div>  
        <div id="describeContact" class="hidden">
          <p>After you submit this form, RITC staff will email you to discuss the types and costs of storage available through <a href="http://rc.dartmouth.edu/index.php/dartfs/" target="_new"><strong>DartFS</strong></a>.</p>
        </div>   
        <div id="describeCustom" class="hidden">
          <h2>Configure your DartFS</h2>
          <div class="params">
            <label>Volume Name <a href="#helpTextName" class="helpName" data-role="button" data-icon="info" data-iconpos="notext" data-rel="popup" data-transition="pop" data-mini="true" data-inline="true"></a></label> <input type="text" data-mini="true" maxlength="15" value="<?php echo $user->get_attribute('volname'); ?>" name="volumeName"><br>
            <label>Storage Requested (in TB)</label>
            <input type="range" name="storageAmount" id="storageAmount" value="1" min="1" max="100" data-theme="a" data-track-theme="a">
          </div>
          <h2>Performance <a href="#helpTextPerformance" class="helpPerformance" data-role="button" data-icon="info" data-iconpos="notext" data-rel="popup" data-transition="pop" data-mini="true" data-inline="true"></a></h2> 
          <div class="centered">
            <fieldset data-role="controlGroup" data-type="horizontal">
                <input id="storageTier3" type="radio" name="storageTier" data-mini="true" value="3" checked>
                <label for="storageTier3">High performance</label>
                <input id="storageTier4" type="radio" name="storageTier" data-mini="true" value="4">
                <label for="storageTier4">Standard performance</label>
            </fieldset>
          </div>
          <h2>Backups <a href="#helpTextBackups" class="helpBackups" data-role="button" data-icon="info" data-iconpos="notext" data-rel="popup" data-transition="pop" data-mini="true" data-inline="true"></a></h2> 
          <div class="centered">
            <fieldset data-role="controlGroup" data-type="horizontal">
                <input id="standardSnapshots" type="radio" name="snapshots" data-mini="true" value="Snapshots" checked>
                <label for="standardSnapshots">Standard snapshots</label>
                <input id="noSnapshots" type="radio" name="snapshots" data-mini="true" value="NoSnapshots">
                <label for="noSnapshots">No snapshots</label>
            </fieldset>
          </div> 
          <div class="estimatedCost">
            <label for="estimate">Estimated Yearly Cost</label>
            <span id="estimate">$0.00</span> <em>(If you have not yet used your 1TB free storage we will deduct <span id="deduction">1TB</span> from this estimate.)</em>
          </div>
        </div>            
        <label>Additional Notes</label>
        <p><em>(Do you need to assign another user as a manager? Are you trying to extend existing DartFS storage? Do you have DartFS storage in one tier but need more storage in another? etc.)</em></p>
        <textarea name="comments"></textarea>
      </section>
      <div class="buttonBar"><button id="back">Back</button><button id="continue">Continue</button><button id="send">Send Request</button></div>
    </form>
    <footer>
      Having trouble with this form? Email us at <a href="mailto:research.computing@dartmouth.edu">research.computing@dartmouth.edu</a><br>
      <img src="images/itc_logo.png">
    </footer>

    <div id="helpTextBackups" class="ui-content" data-role="popup" data-overlay-theme="a" data-dialog="true">
        <div role="main" class="ui-content">
          DartFS creates snapshots of your data on a daily, weekly, and monthly basis rather than full traditional backups that you may be used to on desktop computers. Should you run into trouble, you can recover files from snapshots that we retain as follows:
          <ul>
            <li>Daily snapshots are kept for seven days</li>
            <li>Weekly snapshots are kept for five weeks</li>
            <li>Monthly snapshots are kept for thirteen months</li>
          </ul>
          Snapshots are included for free with standard storage. Custom DartFS storage requests have the option to omit snapshots to reduce storage costs; see the custom request calculator to compare costs. For the safety of your data, we do not recommend disabling snapshots unless you have exceptional circumstances that require doing so.
          <a href="#" data-rel="back" data-role="button">Close</a>
        </div>
    </div>
    <div id="helpTextName" class="ui-content" data-role="popup" data-overlay-theme="a" data-dialog="true">
        <p>By default your DartFS volume name will be <em>LastnameFirstInitial</em>. You may request a custom volume name, subject to these conditions:</p>
        <ul>
          <li>The first character must be an uppercase letter</li>
          <li>Your name cannot contain spaces or other special characters</li>
          <li>Your name cannot be more than 15 characters long</li>
        </ul>
        <a href="#" data-rel="back" data-role="button">Close</a>
    </div>
    <div id="helpTextPerformance" class="ui-content" data-role="popup" data-overlay-theme="a" data-dialog="true">
      <p>DartFS has two performance tiers: high and standard.</p>
      <ul>
        <li><strong>High performance</strong> is most useful when doing storage intensive work like parallel compute jobs on the Discovery cluster.</li> 
        <li><strong>Standard performance</strong> is more cost effective and appropriate for storing and sharing data between desktop computers across the network.</li>
      </ul>
      <p>If you will not be using Discovery or other high performance computing clusters, you may choose standard performance to reduce storage costs.</p>
      <p>Note that if you need to move data to another tier in the future you will need to do so by manually copying the files between the high and standard performance network drives.</p>
      <a href="#" data-rel="back" data-role="button">Close</a>
    </div>  
    <div id="clickwrap" class="ui-content" data-role="popup" data-overlay-theme="a" data-dialog="true">
        Members of the Dartmouth community are expected to be familiar with <a href="https://tech.dartmouth.edu/itc/services-support/help-yourself/knowledge-base/dartmouth-information-technology-policy" target="_new">Dartmouth Computing Polices</a>. By submitting this form, I agree to abide by these policies and all software licenses.
        <a href="#" data-rel="back" data-role="button">Cancel</a>
        <a href="#" id="submitButton" data-role="button">Send Request</a>
    </div>  
    <!-- This is rc-web's GA ID. Not sure if we need a different one -->
    <script>
      window.ga = function () { ga.q.push(arguments) }; ga.q = []; ga.l = +new Date;
      ga('create', 'UA-118695945-1', 'auto'); ga('send', 'pageview')
    </script>
    <script src="https://www.google-analytics.com/analytics.js" async defer></script>
  </div>
</body>
<script>

</script>
</html>