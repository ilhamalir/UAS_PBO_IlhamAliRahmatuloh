<?php
class Fruit {
  public $name;
  public $color;

  function set_details($name, $color) {
    $this->name = $name;
    $this->color = $color;
  }

  function get_details() {
    echo "Name: " . $this->name . ". Color: " . $this->color .".<br>";
  }
}

// Create an object named $apple from the Fruit class
$apple = new Fruit();
$apple->set_details('Apple', 'Red'); // Set property values
$apple->get_details(); // Get output

// Create an object named $banana from the Fruit class
$banana = new Fruit();
$banana->set_details('Banana', 'Yellow'); // Set property values
$banana->get_details(); // Get output
?>
