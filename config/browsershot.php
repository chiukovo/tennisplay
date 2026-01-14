<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Node.js Binary Path
    |--------------------------------------------------------------------------
    |
    | The absolute path to the Node.js binary. Set this if PHP cannot find
    | node in the default paths. You can find the path by running:
    | `which node` on Linux/Mac or `where node` on Windows.
    |
    | Example (Linux): '/usr/bin/node' or '/root/.nvm/versions/node/v18.17.0/bin/node'
    | Example (Windows): 'C:\\Program Files\\nodejs\\node.exe'
    |
    */
    'node_binary' => env('BROWSERSHOT_NODE_BINARY', null),

    /*
    |--------------------------------------------------------------------------
    | NPM Binary Path
    |--------------------------------------------------------------------------
    |
    | The absolute path to the NPM binary. Set this if PHP cannot find
    | npm in the default paths.
    |
    */
    'npm_binary' => env('BROWSERSHOT_NPM_BINARY', null),

    /*
    |--------------------------------------------------------------------------
    | Chrome/Chromium Binary Path
    |--------------------------------------------------------------------------
    |
    | The absolute path to Chrome or Chromium browser. Set this if the auto
    | detection fails. You can find the path by running:
    | `which google-chrome` or `which chromium-browser` on Linux.
    |
    | Example (Linux): '/usr/bin/google-chrome' or '/snap/bin/chromium'
    | Example (Windows): 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe'
    |
    */
    'chrome_binary' => env('BROWSERSHOT_CHROME_BINARY', null),

    /*
    |--------------------------------------------------------------------------
    | Node Modules Path
    |--------------------------------------------------------------------------
    |
    | The path to node_modules directory containing puppeteer.
    | Default is the project's node_modules directory.
    |
    */
    'node_modules_path' => env('BROWSERSHOT_NODE_MODULES_PATH', base_path('node_modules')),
];
