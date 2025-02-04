<?php

class UserLoginTest extends TestCase
{
    /**
     * Test login page is successful
     */
    public function test_login_is_successful(): void
    {
        $this->visit(route('login'))
            ->type($this->test_user_email, 'email')
            ->type($this->test_user_password, 'password')
            ->press('Login')
            ->seePageIs(route('showCreateOrganiser', ['first_run' => '1']));
    }

    /**
     * Test login page is unsuccessful with wrong password
     */
    public function test_login_is_unsuccessful_with_wrong_password(): void
    {
        $this->visit(route('login'))
            ->type($this->test_user_email, 'email')
            ->type('incorrect_password', 'password')
            ->press('Login')
            ->seePageIs(route('login'))
            ->see('Your username/password combination was incorrect');
    }

    /**
     * Test login page is unsuccessful with wrong email address
     */
    public function test_login_is_unsuccessful_with_wrong_email_address(): void
    {
        $this->visit(route('login'))
            ->type('other@email.com', 'email')
            ->type($this->test_user_password, 'password')
            ->press('Login')
            ->seePageIs(route('login'))
            ->see('Your username/password combination was incorrect');
    }
}
