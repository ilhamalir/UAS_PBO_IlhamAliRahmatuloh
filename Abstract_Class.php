<?php
// Abstract base class
abstract class Car {
  public $name;

  // Non-abstract method
  public function __construct($name) {
    $this->name = $name;
  }

  // Abstract method - forces child classes to implement it
  abstract public function intro();
}

// Child class that extends the abstract class
class Audi extends Car {
  public function intro() {
    return "German quality! I'm an $this->name!";
  }
}

// Child class that extends the abstract class
class Citroen extends Car {
  public function intro() {
    return "French extravagance! I'm a $this->name!";
  }
}

// Create objects of the child classes
$audi = new audi("Audi");
echo $audi->intro();
echo "<br>";

$citroen = new citroen("Citroen");
echo $citroen->intro();
?>