<?php

return array(
    'dsn' => 'https://'.env('SENTRY_KEY').'@sentry.io/1215691',
   
    // capture release as git sha
    // 'release' => trim(exec('git log --pretty="%h" -n1 HEAD')),
);