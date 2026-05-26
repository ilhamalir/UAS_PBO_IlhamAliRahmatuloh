<?php
// Define a trait
trait message1 {
  public function msg1() {
    echo "PHP OOP is fun! ";
  }
}

// Use the trait in a class
class Welcome {
  use message1;
}

$obj = new Welcome();
$obj->msg1();
?>