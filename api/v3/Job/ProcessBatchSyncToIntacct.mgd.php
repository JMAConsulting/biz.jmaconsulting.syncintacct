<?php

return array (
  0 =>
  array (
    'name' => 'Cron:Job.ProcessBatchSyncToIntacct',
    'entity' => 'Job',
    'params' =>
    array (
      'version' => 3,
      'name' => 'Sync Batch to Intacct',
      'description' => 'Create GL or AP entries in Intacct for respective contribution or grant payments.',
      'run_frequency' => 'Daily',
      'api_entity' => 'Job',
      'api_action' => 'ProcessBatchSyncToIntacct',
      'parameters' => '',
    ),
  ),
);
