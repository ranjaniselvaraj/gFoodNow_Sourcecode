<?php
/*
 * sample language file for server side error messages of form validation.
 */
$validation_error_messages=array(
		'REQUIRED_ERROR'=>'{caption} is mandatory message from lang file.',
		'CHARONLY_ERROR'=>'Only characters are supported for {caption}',
		'INT_ERROR'=>'Please enter integer value for {caption}',
		'FLOAT_ERROR'=>'Please enter numeric value for {caption}',
		'LENGTHRANGE_ERROR'=>'Length of {caption} must be between {minlength} and {maxlength}',
		'RANGE_ERROR'=>'Value of {caption} must be between {minval} and {maxval}',
		'USERNAME_ERROR'=>'{caption} must start with a letter and can contain only alphanumeric characters (letters, _, ., digits)',
		'PASSWORD_ERROR'=>'{caption} should not contain whitespace characters.',
		'COMPAREWITH_ERROR'=>'{caption} could not be matched',
		'EMAIL_ERROR'=>'Please enter valid email ID for {caption}',
		'USER_REGEX_ERROR'=>'Invalid value for {caption}'
);
?>