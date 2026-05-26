<?php
class Fruit {
  protected $name;

  public function setType($name) {
    $this->name = $name;
  }
}

class Apple extends Fruit {
  public function getType() {
    echo "Name: " . $this->name . ".";
  }
}

$apple = new Apple();
$apple->setType("Apple");
//echo $apple->name; // Error: Cannot access protected property
echo $apple->getType(); // Output: Name: Apple.
?>

