<?php

namespace App\Http\Validators;

use Validator;

abstract class AbstractValidator
{

    /**
     * Validator
     *
     * @var \Illuminate\Validation\Factory
     */
    protected $validator;

    /**
     * Validation data key => value array
     *
     * @var array
     */
    protected $data = array();

    /**
     * Validation errors
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Validation rules
     *
     * @var array
     */
    protected $rules = array();

    /**
     * Validation messages
     *
     * @var array
     */
    protected $messages = array();

    /**
     * Validation codes
     *
     * @var array
     */
    protected $codes = array();

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->before();
        $this->validator = Validator::make($this->data, $this->rules, $this->messages);
        $this->after();
    }

    /**
     * Set data to validate
     *
     * @return validator
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * Set data to validate
     *
     * @return $this
     */
    public function with(array $data)
    {
        $this->data = $data;
        $this->before();
        $this->validator = $this->validator->make($this->data, $this->rules, $this->messages);
        $this->after();
        return $this;
    }

    /**
     * Validation passes or fails
     *
     * @return boolean
     */
    public function passes()
    {
        if ($this->validator->fails())
        {
            $this->errors = $this->validator->messages();

            return false;
        }

        return true;
    }

    /**
     * Return errors, if any
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Return errors codes, if any
     *
     * @return array
     */
    public function getCodes()
    {
        return $this->codes;
    }

    /**
     * getRules
     *
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * getData
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * getErrors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * getMessages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * setRule
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function setRule($key, $value)
    {
        $this->rules[$key] = $value;

        return $this;
    }

    /**
     * emptyRules
     *
     * @return $this
     */
    public function emptyRules()
    {
        $this->rules = array();

        return $this;
    }

    /**
     * sometimes
     * @param  string  $attribute
     * @param  string|array  $rules
     * @param  callable  $callback
     * @return $this
     */
    public function sometimes($attribute, $rules, callable $callback)
    {
        $this->validator->sometimes($attribute, $rules, $callback);

        return $this;
    }

    /**
     * resolver
     * @param  Closure $resolver
     * @return $this
     */
    public function resolver(Closure $resolver)
    {
        Validator::resolver($resolver);

        return $this;
    }

    /**
     * replacer
     * @param  Closure $resolver
     * @return $this
     */
    public function replacer($replace, Closure $resolver)
    {
        Validator::replacer($replace, $resolver);

        return $this;
    }

    /**
     * extendImplicit
     * @param  Closure $resolver
     * @return $this
     */
    public function extendImplicit($extendImplicit, Closure $resolver)
    {
        Validator::extendImplicit($extendImplicit, $resolver);

        return $this;
    }

    /**
     * extend
     * @param  string  $rule
     * @param  \Closure|string  $extension
     * @param  string  $message
     * @return $this
     */
    public function extend($rule, $extension, $message = null)
    {
        Validator::extend($rule, $extension, $message);

        return $this;
    }

    /**
     * before (extend(),resolver())
     * @return $this
     */
    public function before()
    {
        
    }

    /**
     * after(sometimes())
     * @return $this
     */
    public function after()
    {
        
    }

}
