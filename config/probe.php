<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Console Commands
    |--------------------------------------------------------------------------
    |
    | This option allows you to add additional Commander commands that should
    | be available within the Probe environment. Once the command is in
    | this array you may execute the command in Probe using its name.
    |
    */

    'commands' => [
        // App\Console\Commands\ExampleCommand::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Aliased Classes
    |--------------------------------------------------------------------------
    |
    | Probe will not automatically alias classes in your vendor namespaces
    | but you may explicitly allow a subset of classes to get aliased by
    | adding the names of each of those classes to the following list.
    |
    */

    'alias' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Classes That Should Not Be Aliased
    |--------------------------------------------------------------------------
    |
    | Typically, Probe automatically aliases classes as you require them in
    | Probe. However, you may wish to never alias certain classes, which
    | you may accomplish by listing the classes in the following array.
    |
    */

    'dont_alias' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Project Trust Mode
    |--------------------------------------------------------------------------
    |
    | Probe restricts local project features unless your project is trusted.
    | Set this to "always" to avoid untrusted project warnings in Probe.
    | Accepted values: "prompt", "always", "never", true, false, null.
    |
    */

    'trust_project' => env('PROBE_TRUST_PROJECT', 'always'),

];
