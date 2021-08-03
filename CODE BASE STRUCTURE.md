## Code base structure

<p>PHPSandbox CLI is built using <a href="https://laravel-zero.com">Laravel Zero</a> and this makes it very easy for anyone coming from a <a href="laravel.com">Laravel</a> background to get to contributing straight away.</p>

- The `app` directory contains the application logic and it is where all classes are expected to be placed
- `app/Services` directory is where Service classes are placed.  Ideally complex tasks are not expected to be completely run in the console classes. Service classes are created so the console classes primary handle input and output
- `app/Traits` directory is where all traits are placed
- `app/Contracts` directory is where interfaces are placed
- `app/Commands` is where all console classes are placed
- `app/Exceptions` is where all exception classes are placed
- `app/Http` contains the Client class
- The `config` directory contains all configuration files and more can be added only if required

