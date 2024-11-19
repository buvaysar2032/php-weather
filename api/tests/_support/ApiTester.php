<?php

namespace api\tests;

use Codeception\{Actor, Lib\Friend, Util\HttpCode};

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class ApiTester extends Actor
{
    use _generated\ApiTesterActions;

   /**
    * Check current api success response
    */
   public function seeSuccessJsonResponse(): void
   {
       $this->seeResponseIsJson();
       $this->expect('that this is valid success json response');
       $this->seeResponseContains('"success": true');
       $this->seeResponseCodeIs(HttpCode::OK);
   }

    /**
     * Check current api error response
     */
   public function seeErrorJsonResponse(int $code): void
   {
       $this->seeResponseIsJson();
       $this->expect('that this is valid json response with error code');
       $this->seeResponseContains('"success": false');
       $this->seeResponseCodeIs($code);
   }
}
