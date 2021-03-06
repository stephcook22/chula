<?php
use \WebGuy;

class AdminCest
{

    public function _before()
    {
    }

    public function _after()
    {
    }

    // tests
    public function checkLoginPageHasValidElements(WebGuy $I)
    {
        $I->am('Administrator');
        $I->wantTo('Check the login page has the correct elements');
        $I->amOnPage('/login');
        $I->see('Please sign in', 'h2');
        $I->seeElement("form input[name=_username]");
        $I->seeElement("form input[name=_password]");
        $I->seeElement("form input[type=submit]");
    }

    public function tryToLoginWithValidDetails(WebGuy $I)
    {
        $I->am('Administrator');
        $I->wantTo('Check that I can login, I will provide valid credentials');
        AdminLoginPage::of($I)->login('admin', 'foo');
        $I->see('Your pages');
        $I->see('Drafts');
    }

    public function tryToLoginWithInvalidDetails(WebGuy $I)
    {
        $I->am('Administrator');
        $I->wantTo('I am not allowed to login with invalid credentials');
        AdminLoginPage::of($I)->login('admin', '123');
        $I->expect('Login not allowed due to incorrect credentials');
        $I->see('Bad credentials');
    }

}