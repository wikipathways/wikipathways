<?php
/**
 * Generate the JNLP file required to open the pathway
 * specified by the identifier query parameter
 *
 * Reference page:
 * https://docs.oracle.com/javase/tutorial/deployment/webstart/deploying.html
 * Github issue:
 * https://github.com/PathVisio/pathvisio/issues/5
 */

  ini_set("error_reporting", 0);

  if (isset($_GET['identifier'])) {
    $identifier = htmlspecialchars($_GET['identifier']);
  }
  else {
    $identifier = 'WP4';
  }

  // see http://schema.org/version
  if (isset($_GET['version'])) {
    $version = htmlspecialchars($_GET['version']);
  }
  else {
    $version = 0;
  }

  if (isset($_GET['filename'])) {
    $filename = htmlspecialchars($_GET['filename']);
  }
  else {
    if ($identifier) {
      $filename = $identifier.'v'.$version;
    }
    else {
      $filename = 'PathVisio';
    }
  }

  header('Content-Disposition: attachment; filename="'.$filename.'.jnlp"');
  header('Content-Type: application/force-download');
  header('Content-Transfer-Encoding: binary');

  // XML JNLP
  $template_path = 'http://www.pathvisio.org/data/releases/current/webstart/pathvisio.jnlp';
  $jnlp = simplexml_load_file($template_path);
  //$jnlp['href'] = 'pathway-jnlp.php?identifier='.$identifier.'&version='.$version.'&filename='.$filename;
  unset($jnlp['codebase']);
  unset($jnlp['href']);
  $resources_el = $jnlp->{'resources'};
  $jar_el = $resources_el->{'jar'};
  $jar_el['href'] = 'http://www.pathvisio.org/data/releases/current/webstart/pathvisio.jar';
  $application_desc_el = $jnlp->{'application-desc'};
  $application_desc_el->argument[0] = '-wpid';
  $application_desc_el->argument[1] = $identifier;
  $application_desc_el->argument[2] = '-wprev';
  $application_desc_el->argument[3] = $version;
  echo $jnlp->asXML();
?>
