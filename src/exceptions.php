<?php

class PermyException extends \Exception
{
    //
}

class PermyFileCreateException extends PermyException {}
class PermyFileUpdateException extends PermyException {}
class PermyMethodNotSetException extends PermyException {}
class PermyControllerNotSetException extends PermyException {}
class PermyPermissionsNotFoundException extends PermyException {}
class PermyUserNotSetException extends PermyException {}
