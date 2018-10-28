<?php

class FormHelper
{
    protected $values = array();

    public function __construct($values = array())
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->values = $_POST;
        } else {
            $this->values = $values;
        }
    }

    public function input($type, $attribute = array(), $isMultiple = false)
    {
        $attribute['type'] = $type;
        if (($type == 'radio') || ($type == 'checkbox'))
        {
            if ($this->isOptionSelected($attribute['name'] ?? null, $attribute['value'] ?? null))
            {
                $attribute['checked'] = true;
            }
        }
        return $this->tag('input', $attribute, $isMultiple);
    }

    public function select($options, $attribute = array())
    {
        $multiple = $attribute['multiple'] ?? false;

        return
            $this->start('select', $attribute, $multiple) .
            $this->options($attribute['name'] ?? null, $options) .
            $this->end('select');
    }

    public function textarea($attribute = array())
    {
        $name = $attribute['name'] ?? null;
        $value = $this->values['name'] ?? '';

        return
            $this->start('textarea', $attribute) .
            htmlentities($value) .
            $this->end('textarea');
    }

    public function tag($tag, $attributes = array(), $isMultiple = false)
    {
        return "<$tag {$this->attributes($attributes, $isMultiple)}/>";
    }

    public function start($tag, $attributes = array(), $isMultiple = false)
    {
        //<select>と<testarea>タグは値属性を持たない
        $valueAttribute = (! (($tag == 'select') || ($tag == 'textarea')));
        $attrs = $this->attributes($attributes, $isMultiple, $valueAttribute);

        return "<$tag $attrs>";
    }

    public function end($tag)
    {
        return "</$tag>";
    }

    protected function attributes($attributes, $isMultiple, $valueAttribute = true)
    {
        $tmp = array();
        //このタグに値属性を指定することができ、タグが名前を持ち、値属性にこの名前のエントリがあれば、値属性を設定する
        if ($valueAttribute && isset($attributes['name']) && array_key_exists($attributes['name'], $this->values))
        {
            $attributes['value'] = $this->values[$attributes['name']];
        }
        foreach ($attributes as $k => $v)
        {
            //真偽値trueはブール属性を意味する
            if (is_bool($v)) {
                if ($v) {
                    $tmp[] = $this->encode($k);
                }
            } else {
                $value = $this->encode($v);
                if ($isMultiple && ($k == 'name')){
                    $value .= '[]';
                }
                $tmp[] = "$k=\"$value\"";
            }
        }
        return implode('', $tmp);
    }

    protected function options($name, $options)
    {
        $tmp = array();
        foreach ($options as $k => $v)
        {
            $s = "<option value=\"{$this->encode($k)}\"";
            if ($this->isOptionSelected($name, $k)){
                $s .= ' selected';
            }
            $s .= ">{$this->encode($v)}<\option>";
            $tmp[] = $s;
        }
        return implode('', $tmp);
    }

    protected function isOptionSelected($name, $value)
    {
        //値配列に$nameのエントリがなければ、このオプションは選択できない
        if (! isset($this->values['name'])){
            return false;
        }
        //値配列に$nameのエントリが配列の場合、$valueがその配列にあるかどうか調べる
        elseif (is_array($this->values['name'])){
            return in_array($value, $this->values['name']);
        }
        //それ以外なら$valueと値配列の$nameのエントリを比較する
        else {
            return $value == $this->values['name'];
        }
    }

    protected function encode($s)
    {
        return htmlentities($s);
    }
}