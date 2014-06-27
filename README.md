JasTPL ?	
=====================

> JasTPL is an open source PHP template engine, fast and easy to use.


----------

Installation
------------

 - Allow using global variables ( without using assign() )

```php
require_once "Class.Jas-Templater.php";
$tpl = new JasTpl();
$tpl->Tpl_Dir   = "tpls/";
$tpl->Cache_Dir = "temp/";
```

 - disallow global variables

```php

require_once "Class.Jas-Templater.php";
$tpl = new JasTpl(null, null, false);
$tpl->Tpl_Dir   = "tpls/";
$tpl->Cache_Dir = "temp/";
```
----------


Tags
----

 - Variables :

```php
$tpl->assign('foo', 'Hello world!'); // if global vars = false
```


> The value of foo variable is : {$foo}


----------


 - If condition
 
 Example :
```html

{if $foo == "Hello world" }

    world  !
    
{elseif $foo == "Hello Github"}

    github !

{else}

    Null   !

{/if}
```

 - LOOP / SECTION

  Example :
  
```php

$data = array(
          array('id' => 1, 'fullname' => 'Jad #1', 'email' => 'email_1@email.com'),
          array('id' => 2, 'fullname' => 'Jad #2', 'email' => 'email_2@email.com'),
          array('id' => 3, 'fullname' => 'Jad #3', 'email' => 'email_3@email.com'),
          array('id' => 4, 'fullname' => 'Jad #4', 'email' => 'email_4@email.com')
        );

$tpl->assign('contacts', $data);

```
  
```html
<ul>
{loop name=at loop=contacts}
    <li>
        ID : {contacts[at].id} , 
        full name : {contacts[at].fullname} ,
        email : {contacts[at].email} 
    <li>
{/loop}
</ul>
```
 * section tag is an alias of loop, you can use it !
 


----------


 
 * Looping using foreach :
 
```php

$pers = array(1 => 'Steve', 2 => 'Bill', 3 => 'Mark');
$tpl->assign('list', $pers);

```


```html 
<ul>
 {foreach from=list key=i name=val}
 <li> {$i}: {$val}</li>
 {/foreach}
</ul>

```

 - Include a file 
 
```html 

 {include file='second_file.tpl'}
 
 
```



 
 

----------


