<?php
require_once 'lib.php';
require_once 'functions.php';

def_printfer('h1', "<h1>%s</h1>\n");
h1('Hello, ');
h1('World!');
# <h1>Hello, </h1>
# <h1>World!</h1>

def_printfer('puts', "%s\n");
puts("text");
# text



defun('say', function ($what, $what2){
	printf("ME: %s, %s\n", $what, $what2);
});
say('one','two');
// ME: one, two

defun('say', function ($what, $what2){
	printf("ME>>: %s, %s\n", $what, $what2);
});
say('one','two');
// ME>>: one, two

def_wrap('say', function($call){
	foreach($call->args as $k=>$v)
		$call->args[$k] = strtoupper($v);
	$call();
});
say('one','two');
// ME>>: ONE, TWO

def_wrap('say', function($call){
	foreach($call->args as $k=>$v)
		$call->args[$k] = '_'.$v.'_';
	$call();
});
say('one', 'two');
// ME>>: _ONE_, _TWO_

unwrap('say');
say('one', 'two');
// ME>>: ONE, TWO

unwrap('say');
say('one', 'two');
// ME>>: one, two

unwrap('say');
say('one', 'two');
//ME: one, two


def_memo('sum', function($one, $two){
	echo "Summing: {$one} - {$two} \n";
	return $one+$two;
});

puts('Result: '.sum(1, 2));
puts('Result: '.sum(1, 2));
puts('Result: '.sum(2, 2));
# Summing: 1 - 2 
# Result: 3
# Result: 3
# Summing: 2 - 2 
# Result: 4


///////////////////////////////////
def_converter('id','object',function($i){
		//return Model::find($i);
		return (object)$i;
});

print_R(id_to_object(13));
# stdClass Object
# (
#    [scalar] => 13
# )

def_converter('id','object',function($i){
		//return Model::find($i);
		return (object)$i;
});

print_R(id_to_object(1));
print_R(ids_to_objects(array(2,3)));
/*
stdClass Object
(
    [scalar] => 1
)
Array
(
    [0] => stdClass Object
        (
            [scalar] => 2
        )

    [1] => stdClass Object
        (
            [scalar] => 3
        )

)
*/


def_sprintfer('a', "<a href='%s'>%s</a>");
def_sprintfer('img', "<img src='%s'>");

defun('def_tag',function($name){
	def_sprintfer($name, "<{$name}>%s</{$name}>");
});

foreach(array('p','div','html','head','body','title', 'h1') as $tag)
	def_tag($tag);

//////////////////////////////////////////////////

echo html(head(title('Hello, World!')).
          body(div(h1('Hello, World!')).
	       div(p("This is a page about world!").
	           a("http://world.com", img("http://world.com/logo.jpg")))));