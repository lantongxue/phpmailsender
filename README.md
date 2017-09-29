# phpmailsender
easy php mail sender

# feature
1. native socket implementation
2. support SSL/TLS 
3. support HTML code
4. small size only 10kb
5. single file, simple reference
6. easy expansion
7. support php all version (recommend 5.3.0 and later)
8. ......
# defect
1. attachments are not supported
# example
```php
$options = array(
	'smtp_host' => 'mail.xx.com',
	'smtp_port' => 25,
	'user_name' => 'test@mail.xx.com',
	'password'  => '1',
	'isHtml'    => true,
);

$m = new Mailer($options);

$m->setOption('debug', true);
//$m->enableSSL(true);
//$m->enableTLS(true);
// $to = array(
//     'test3@mail.xx.com',
//     'test4@mail.xx.com',
//     array('label'=>'test6','address'=>'test6@mail.xx.com'),
// );
// $m->setTo($to);

$m->setTo('test2@mail.xx.com');

//$m->setBCC('test4@mail.xx.com');
//$m->setCC('test3@mail.xx.com');

//$m->setFrom('test3@mail.xx.com');
$m->setFrom(array('label'=>'Admin Group','address'=>'test3@mail.xx.com'));

$m->setSubject('code:'.rand());

$m->setBody(rand().'test  body<h1>html</h1>');

$m->send();
```
