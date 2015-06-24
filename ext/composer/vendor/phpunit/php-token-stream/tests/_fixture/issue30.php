<?php
class Foo
{
    public function bar()
    {
    	return get_class($this);
        //return Foo::CLASS;
    }
}
