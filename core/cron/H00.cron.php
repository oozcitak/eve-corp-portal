<?php

require_once(dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR."core.class.php");
$core_305d0899_7443_4b39_a8c3_12db2bdf1e15 = new Core();

$files_305d0899_7443_4b39_a8c3_12db2bdf1e15 = $core_305d0899_7443_4b39_a8c3_12db2bdf1e15->ReadCronJobsForType(0);

$root_305d0899_7443_4b39_a8c3_12db2bdf1e15 = dirname(dirname(dirname(__FILE__)));

foreach($files_305d0899_7443_4b39_a8c3_12db2bdf1e15 as $file_305d0899_7443_4b39_a8c3_12db2bdf1e15)
{
  // Real path to the source script
  if(substr($file_305d0899_7443_4b39_a8c3_12db2bdf1e15->Source, 0, 1) != "/")
    $file_305d0899_7443_4b39_a8c3_12db2bdf1e15->Source = "/".$file_305d0899_7443_4b39_a8c3_12db2bdf1e15->Source;
  $path_305d0899_7443_4b39_a8c3_12db2bdf1e15 = $root_305d0899_7443_4b39_a8c3_12db2bdf1e15.$file_305d0899_7443_4b39_a8c3_12db2bdf1e15->Source;
  
  if(file_exists($path_305d0899_7443_4b39_a8c3_12db2bdf1e15))
  {
    // Cache the output for error reporting
    ob_start();
    $source_305d0899_7443_4b39_a8c3_12db2bdf1e15 = file_get_contents($path_305d0899_7443_4b39_a8c3_12db2bdf1e15);
    // Strip PHP tags
    if(substr($source_305d0899_7443_4b39_a8c3_12db2bdf1e15, 0, 5) == "<?php")
      $source_305d0899_7443_4b39_a8c3_12db2bdf1e15 = substr($source_305d0899_7443_4b39_a8c3_12db2bdf1e15, 5);
    if(substr($source_305d0899_7443_4b39_a8c3_12db2bdf1e15, -2) == "?>")
      $source_305d0899_7443_4b39_a8c3_12db2bdf1e15 = substr($source_305d0899_7443_4b39_a8c3_12db2bdf1e15, 0, strlen($source_305d0899_7443_4b39_a8c3_12db2bdf1e15) - 2);
    // Evaluate the code
    $result_305d0899_7443_4b39_a8c3_12db2bdf1e15 = eval($source_305d0899_7443_4b39_a8c3_12db2bdf1e15);
    // Save the output buffer
    $output_305d0899_7443_4b39_a8c3_12db2bdf1e15 = ob_get_clean();
    $core_305d0899_7443_4b39_a8c3_12db2bdf1e15->SaveCronResult($file_305d0899_7443_4b39_a8c3_12db2bdf1e15->ID, $output_305d0899_7443_4b39_a8c3_12db2bdf1e15);
    // Log error
    if(is_bool($result_305d0899_7443_4b39_a8c3_12db2bdf1e15) && ($result_305d0899_7443_4b39_a8c3_12db2bdf1e15 == FALSE))
      $core_305d0899_7443_4b39_a8c3_12db2bdf1e15->Log("CRON(".$file_305d0899_7443_4b39_a8c3_12db2bdf1e15->Title.") Error executing script.".$output_305d0899_7443_4b39_a8c3_12db2bdf1e15);
  }
  else
    $core_305d0899_7443_4b39_a8c3_12db2bdf1e15->Log("CRON(".$file_305d0899_7443_4b39_a8c3_12db2bdf1e15->Title.") Script file not found.");
    
  // Save last run time
  $core_305d0899_7443_4b39_a8c3_12db2bdf1e15->SaveCronLastRunTime($file_305d0899_7443_4b39_a8c3_12db2bdf1e15->ID);
}

?>
