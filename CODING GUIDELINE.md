## Coding Guideline

<ol>
<li>Code style must follow <a href="http://www.php-fig.org/psr/psr-1/">PSR-1</a>, <a href="http://www.php-fig.org/psr/psr-2/">PSR-2</a> and <a href="https://www.php-fig.org/psr/psr-12/">PSR-12</a>.</li>
<li>a file suffix should be sufficient to know what it does for instance an Exception class should have Exception as it suffix, a service class should have Service as it suffix, a Command class should have Command as it suffix.</li>
<li>All Service classes must be well tested with tests for all their public methods defined in the tests/Unit directory.</li>
<li>Command classes must be well tested with tests placed in the tests/Feature directory.</li>
<li>app/Services directory is where Service classes are placed.  Ideally complex tasks are not expected to be completely run in the console classes. Service classes are created so the console classes primary handle input and output.</li>
<li>Doc blocks should only be used when they are required to give better context of the method or class. We believe that using clear and concise class and variable names with proper arguments and return type declaration should suffice. If a method returns nothing, then it return type must be declared as void.</li>
<li>api endpoints in the Client class are defined as class properties while methods that fetch resources from the api should have the get prefix. Such method should also be clear on what resource it is fetching.</li>
<li>Our coding guideline is also inspired by <a href="https://spatie.be/guidelines/laravel-php">spatie coding guideline</a>. You should check it out.</li>
</ol>

