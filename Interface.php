<?php
// Define the interface
interface Animal {
  public function makeSound();
}

// Implement the interface in a class
class Cat implements Animal {
  public function makeSound() {
    echo "Meow";
  }
}

// Implement the interface in another class
class Dog implements Animal {
  public function makeSound() {
    echo "Woff";
  }
}

$cat = new Cat();
$cat->makeSound();

$dog = new Dog();
$dog->makeSound();
?>