
PHP Form Helper
============================

The form helper is designed to help programmers to simplify the back-end process flow of data related to HTML form. It provides convenient methods to read data from `$_POST` or `$_GET` array, to quickly dump the form data into a domain object which can be saved in to database later on, and to encourage the seperation of the code about how the form looks like and the code about assigning a value to an input in the form.

**Requirement:** PHP 5.1+

**License:** Free for commercial and non-commercial use ([Apache License](http://www.apache.org/licenses/LICENSE-2.0.html) or [GPL](http://www.gnu.org/licenses/gpl-2.0.html))

**Note:** The following tutorial uses short array syntax `[]` which is available in PHP 5.4+.

Basic Usage
---
Let's declare a book form and its three inputs with initial values in `init()` method. Remember to inherit the parent class `FormHelper`.

```php
require("php-form-helper/form.php");
class BookForm extends FormHelper {
  function init() {
    $this->input("name", "My Book");
    $this->input("desc", "Its Description");
    $this->input("lang", "English");
  }
}
```
Then initialize a form object `$f`, and use `renderNameValue` to generate a name-value pair which follows the format of attributes in an HTML input tag.

```php
$f = new BookForm; // while constructing an object, the init() method will be called automatically.
$f->renderNameValue("name"); // equals to echo 'name="name" value="MyBook"'
$f->renderNameValue("desc"); // equals to echo 'name="desc" value="Its Description"'
$f->renderNameValue("lang"); // equals to echo 'name="lang" value="English"'
```
Hence the code:

```html
	<input id="name" <?php $f->renderNameValue("desc") ?> />
```
will generate

```html
	<input id="name" name="desc" value="Its Description" />
```
The form helper will take care of the generation of name-value pair for you.

Change the value of an input
---
Sometimes we need to change the value of an input which belongs to a form helper, use `set` method.

```php
	$f->set("desc","New Description");
	$f->renderNameValue("desc"); // name="desc" value="New Description"
```

**Advantage:** So far, you can see that the design of form helper encourages the seperation of UI code (the HTML code about how the form looks like) and logic code (PHP code about assigning a value to an input in the form) by introducing a form helper as the mediator between them. You can benefit from it when your site change frequently.

Seperate the render of name and value of an input
---
Use `$f->get("input-name")` in some occasions like textarea which put its content in the inner HTML

```html
	<textarea name="desc">
	<?php echo $f->get("desc");?>
	</textarea>
```


Preparation to the Toturial Below
--

A form class describes its 3 inputs: name, desc, lang.

```php
class BookForm extends FormHelper {
  function init() {
    $this->input("name");
    $this->input("desc");
    $this->input("lang");
  }
}
```

A domain class represents its table in database

```php
class Book {
  public name;
  public desc;
  public lang;
  public static insert(Book b) {
    $db->insert(
      "INSERT INTO books (name,desc,lang) VALUES (?,?,?)",
      [$b->name,$b->desc,$b->lang]
    );	
  }
  public static find_by_id($id) {
    return $db->fetchOneObj("SELECT * FROM books WHERE id = ?",[$id]);
  }
}
```

Read data from POST array and display it on an HTML form
---

The `importFromArray` method will set the value of input in `$f` with the value of a key, having the same name as the input, in the given array.

php code:

```php

$f->importFromArray($_POST);
```

html code:

```html
<form action="<?php $PHP_SELF ?>"  method="POST" >

  <input type="text" <?php $f->renderNameValue("name"); ?> />
  <textarea name="desc"><?php echo $f->get("desc");?></textarea>
  <input type="text" <?php $f->renderNameValue("lang"); ?> />
</form>

```

Read data from database and display it on an HTML form
---

The `importFromModel` method will set the value of input in `$f` with the value of attribute, having the same name as the input, in a domain object.

php code:

```php
	$book = Book::find_by_id(3);
	$f->importFromModel($book);
```

the HTML code is the same as previous section.

Save data from POST array to database
---
Firstly, use `isEntirelyIn` method to check whether the `$_POST` array contains all names of inputs that described in `$f`.If so, read the corresponding values from the `$_POST` array into form helper `$f`.And export the data into a book object, then save it.

```php

$f = new BookForm();

if($f->isEntirelyIn($_POST)) {
  $f->importFromArray($_POST);
  $book = $f->exportToModel(Book);
  Book::insert($book);
}
```

Methods of FormHelper
---
I list all the methods in the class `FormHelper`, thoese methods are also avaible to all subclasses that inherit it.

**Input declaration**

|Method|Description|
|------|-----------|
|`input($name,$value="")`||

**Basic value getter / setter and contain?**

|Method|Description|
|------|-----------|
|`get($name)`||
|`getInt($name)`||
|`contains($input_name)`||
|`getValues()`||
|`setValues($arr)`||

**about HTML form rendering**

|Method|Description|
|------|-----------|
|`renderNameIntValue($name)`||
|`renderNameValue($name) `||

**Array importing / examination**

|Method|Description|
|------|-----------|
|`isEntirelyIn($arr)`||
|`isPartiallyIn($arr)`||
|`importFromArray($arr)`||

**Domain model importing / exporting**

|Method|Description|
|------|-----------|
|`exportToModel($className, $form_prefix = "", $form_suffix = "")`||
|`importFromModel($model, $form_prefix = "", $form_suffix = "")`||


