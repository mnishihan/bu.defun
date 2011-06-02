<?php
use bu\def\Memo;
def('undef', function($name){
		array_pop(Memo::$fns[$name]);
		end(Memo::$fns[$name]);
	});

def('deflet', function($fn){
		$fns = Memo::$fns;
		$return = $fn();
		Memo::$fns = $fns;

		return $return;
	});

def('def_alias', function($orig, $dest){
		def($dest, function() use($orig){
				$args = func_get_args();
				return call_user_func_array($orig, $args);
			});
	});

def('def_wrapper', function($name, $fn){
		$old_fn = current(Memo::$fns[$name]);
		def($name,
		    function() use($old_fn, $fn){
			    $args = func_get_args();
			    $old_fn->args = $args;
			    return $fn($old_fn);
		    });
	});

def_alias('undef', 'undef_wrapper');

def('def_printfer', function($name, $tpl){
		def($name, function() use($tpl){
				$args = func_get_args();
				array_unshift($args, $tpl);
				return call_user_func_array('printf', $args);
			});
	});

def('def_sprintfer', function($name, $tpl){
		def($name, function() use($tpl){
				$args = func_get_args();
				array_unshift($args, $tpl);
				return call_user_func_array('sprintf', $args);
			});
	});

def('def_memo', function($name, $fn){
		def($name, function() use($name, $fn){
				static $data = array();
				$args = func_get_args();
				$key = serialize($args);
				if(!isset($data[$name][$key]))
					$data[$name][$key] = call_user_func_array($fn, $args);
				return $data[$name][$key];
			});
	});

def('def_converter', function($from, $to, $fn){
		def("{$from}_to_{$to}", $fn);
		def("{$from}s_to_{$to}s", function($array) use ($fn){
				return array_map($fn, $array);
			});
	});

// Объявить функцию $name, которая просто вернёт $value.
def('def_return', function($name, $value){
		def($name, function() use($value){
				return $value;
			});
	});

def('def_text_inspector', function($fn){
		def_wrapper($fn, function($call) use($fn){
				echo ">> Calling '{$fn}' function with arguments:\n";
				foreach($call->args as $arg)
					echo ">> - {$arg}\n";
				$r = $call();
				echo ">> return value is '{$r}'\n";
				return $r;
			});
	});

def('def_antonyms', function($true, $false, $fn){
		def($true, $fn);
		def($false, function() use($fn){
				$r = call_user_func_array($fn, func_get_args());
				if($r === true)
					return false;
				elseif($r === false)
					return true;
				return $r;
			});
	});

def('_catch', function($what, $fn){
		try{
			$fn();
		}catch(bu\def\Signal $e){
			if($e->what == $what)
				return;
			throw $e;
		}
	});

def('_throw', function($what){
		throw new bu\def\Signal($what);
	});

def('def_state_fns', function($positive, $negative,
			      $setter, $getter){
	    def($positive, function() use($setter, $getter){
			    $return = $getter();
			    if(func_num_args()){
				    $arg = func_get_arg(0);
				    if($arg != $return)
					    $setter($arg);
			    }
			    return $return;
		    });
	    def($negative, function() use($setter, $getter){
			    $return = !$getter();
			    if(func_num_args()){
				    $arg = func_get_arg(0);
				    if($arg != $return)
					    $setter(!$arg);
			    }
			    return $return;
		    });
	});

def('import_ns', function($ns){
		$fns = get_defined_functions();
		$fns = $fns['user'];
		foreach($fns as $fn){
			if(strstr($fn, $ns) !== false){
				$fn_name = end(explode('\\', $fn));
				def_alias($fn, $fn_name);
			}
		}
	});

def('import', function($class_nm){
		foreach($class_nm::$fns as $name=>$fn)
			def($name, $fn);
	});

def('catcher', function($nm, $fn){
		Memo::$catchers[$nm] = $fn;
	});

def('noise', function($nm){
		if(isset(Memo::$catchers[$nm])){
			$args = func_get_args();
			array_shift($args);
			return call_user_func_array(Memo::$catchers[$nm], $args);
		}
	});

def('signal', function($nm){
		if(isset(Memo::$catchers[$nm])){
			$args = func_get_args();
			array_shift($args);
			return call_user_func_array(Memo::$catchers[$nm], $args);
		}else{
			throw new bu\def\UncatchedSignalException("Uncatched signal '$nm'");
		}
	});

def('module', function($nm, $block){
		$prefix = Memo::$prefix;
		Memo::$prefix = $nm.'::';
		$block();
		Memo::$prefix = $prefix;
	});