
PHP Form Helper
============================

The form helper is designed to help programmers to simplify the back-end process flow of data related to HTML form. It provides convenient methods to read data from `$_POST` or `$_GET` array, to quickly dump the form data into a domain object which can be saved into database later on, and it encourages the seperation of the code about how the form looks like and the code about assigning a value to an input in the form.

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

Seperate the rendering of name and value of an input
---
Use `$f->get("input-name")` in some occasions like textarea which put its content in the inner HTML

```html
	<textarea name="desc">
	<?php echo $f->get("desc");?>
	</textarea>
```


Preparation to toturials in the following sections
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

The `importFromArray` method will give the input in `$f` the value of the key which has the same name as the input, in the given array.

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

The `importFromModel` method will give the input in `$f` the value of the attribute which has the same name as the input, in a domain object.

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
I list down all the methods in the class `FormHelper`, thoese methods are also avaible to all subclasses that inherit it.

**Input Declaration**

|Method|Description|
|------|-----------|
|`input(string $name, string $value="") : void`| register an input with its name and an optional value|

**Basic Operations Against Form Value(s)**

|Method|Description|
|------|-----------|
|`get(string $name) : string`| get the value of input by its name|
|`getInt(string $name) : int`| get the int value of input by its name |
|`contains(string $input_name) : boolean`| check if helper contains an input with a given name |
|`getValues() : array`| returns a key-value hash about input name and its value |
|`setValues(array $arr) : void`| set helper's inernal array with a given input-name-to-value hash |

**HTML Form Rendering**

|Method|Description|
|------|-----------|
|`renderNameIntValue(string $name) : string`| print out a name-value pair in the form of HTML attribute |
|`renderNameValue(string $name) : string `| print out a name-int-value pair in the form of HTML attribute |

**Importation and Examination of Source Data in Array**

|Method|Description|
|------|-----------|
|`isEntirelyIn(array $arr) : boolean`| check if the given array contains all names of inputs that described in this helper |
|`isPartiallyIn(array $arr) : boolean`| check if the given array contains at least one name of inputs that described in this helper |
|`importFromArray(array $arr) : void`| read the corresponding values from the given array into form helper|

**Domain Model Importing and Exporting**

`exportToModel(string $className, string $input_prefix = "", string $input_suffix = "") : className`

* Export the corresponding values from a form helper into a domain object by giving its class name. The usage of `$input_prefix` and `$input_suffix` is detailed in the following section.

`importFromModel(string $model, string $input_prefix = "", string $input_suffix = "") : void`

* Import the corresponding values from an instance of a domain class into a form helper. The usage of `$input_prefix` and `$input_suffix` is the same as `exportToModel` method.

Matching prefix or suffix while exporting to domain object
---
If a form contains many domain objects that differ in prefix and suffix of input names, the FormHelper can also help you sperate them into different objects.

Consider the following HTML code showing that the two object instances of `Book` class are contained in one form.

```html
<form>
  <input name="eng_book_name_1" value = "My English book 1" />
  <input name="eng_book_desc_1" value = "The description about My English book 1" />
  <input name="eng_book_lang_1" value = "English" />
  
  <input name="cht_book_name_2" value = "My traditional chinese book 2" />
  <input name="cht_book_desc_2" value = "The description about my traditional chinese book  2" />
  <input name="cht_book_lang_2" value = "Traditional Chinese" />
  
</form>
```
And the corresponding form class is `BooksForm` described below.

```php

class BooksForm extends FormHelper {
	function init() {
    	$this->input("eng_book_name_1");
    	$this->input("eng_book_desc_1");
        $this->input("eng_book_lang_1");
        
        $this->input("cht_book_name_2");
    	$this->input("cht_book_desc_2");
        $this->input("cht_book_lang_2");
    }

}
```
As a reminder, the `Book` class is the same as which we mentioned in other section before.

```php
class Book {
  public name;
  public desc;
  public lang;
  /* the remaining code is omitted*/
 }
```
Now, we are going to extract the data of books from `$_POST` array by specifing prefix and suffix in 2nd and 3rd argument of `exportToModel` method respectively.

```php
$f = new BooksForm
$f->importFromArray($_POST);

$eng_book_1 = $f->exportToModel("Book","eng_book","1");
echo $eng_book_1->name; // My English book 1
echo $eng_book_1->desc; // The description about My English book 1
echo $eng_book_1->lang; // English

$cht_book_2 = $f->exportToModel("Book","cht_book","2");
echo $cht_book_2->name; // My traditional chinese book 2
echo $cht_book_2->desc; // The description about my traditional chinese book  2
echo $cht_book_2->lang; // Traditional Chinese

```
