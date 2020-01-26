<?php
namespace Derhansen\FeChangePwd\Tests\Unit\Validation\Validator;

/*
 * This file is part of the Extension "fe_change_pwd" for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Derhansen\FeChangePwd\Domain\Model\Dto\ChangePassword;
use Derhansen\FeChangePwd\Service\LocalizationService;
use Derhansen\FeChangePwd\Service\OldPasswordService;
use Derhansen\FeChangePwd\Service\SettingsService;
use Derhansen\FeChangePwd\Validation\Validator\ChangePasswordValidator;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Class ChangePasswordValidatorTest
 */
class ChangePasswordValidatorTest extends UnitTestCase
{
    /**
     * @var ChangePasswordValidator
     */
    protected $validator;

    /**
     * Setup
     *
     * @return void
     */
    protected function setup()
    {
        parent::setUp();
        $this->validator = $this->getAccessibleMock(
            ChangePasswordValidator::class,
            ['translateErrorMessage', 'getValidator'],
            [],
            '',
            false
        );
    }

    /**
     * @return array
     */
    public function validatePasswordComplexityDataProvider()
    {
        return [
            'no password given' => [
                '',
                '',
                true,
                []
            ],
            'passwords not equal' => [
                'password1',
                'password2',
                true,
                []
            ],
            'length below min length' => [
                'password',
                'password',
                true,
                [
                    'passwordComplexity' => [
                        'minLength' => 9,
                        'capitalCharCheck' => 0,
                        'lowerCaseCharCheck' => 0,
                        'digitCheck' => 0,
                        'specialCharCheck' => 0,
                    ]
                ]
            ],
            'no capital char' => [
                'password',
                'password',
                true,
                [
                    'passwordComplexity' => [
                        'minLength' => 7,
                        'capitalCharCheck' => 1,
                        'lowerCaseCharCheck' => 0,
                        'digitCheck' => 0,
                        'specialCharCheck' => 0,
                    ]
                ]
            ],
            'no lower case char' => [
                'PASSWORD',
                'PASSWORD',
                true,
                [
                    'passwordComplexity' => [
                        'minLength' => 7,
                        'capitalCharCheck' => 0,
                        'lowerCaseCharCheck' => 1,
                        'digitCheck' => 0,
                        'specialCharCheck' => 0,
                    ]
                ]
            ],
            'no digit' => [
                'password',
                'password',
                true,
                [
                    'passwordComplexity' => [
                        'minLength' => 7,
                        'capitalCharCheck' => 0,
                        'lowerCaseCharCheck' => 0,
                        'digitCheck' => 1,
                        'specialCharCheck' => 0,
                    ]
                ]
            ],
            'no special char' => [
                'password',
                'password',
                true,
                [
                    'passwordComplexity' => [
                        'minLength' => 7,
                        'capitalCharCheck' => 0,
                        'lowerCaseCharCheck' => 0,
                        'digitCheck' => 0,
                        'specialCharCheck' => 1,
                    ]
                ]
            ],
            'strong password' => [
                'Th!s_i$_a_$+r0ng_passw0rd#',
                'Th!s_i$_a_$+r0ng_passw0rd#',
                false,
                [
                    'passwordComplexity' => [
                        'minLength' => 20,
                        'capitalCharCheck' => 1,
                        'lowerCaseCharCheck' => 1,
                        'digitCheck' => 1,
                        'specialCharCheck' => 1,
                    ]
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider validatePasswordComplexityDataProvider
     */
    public function validatePasswordComplexityTest($password1, $password2, $expected, $settings)
    {
        $changePassword = new ChangePassword();
        $changePassword->setPassword1($password1);
        $changePassword->setPassword2($password2);

        $mockSettingsService = $this->getMockBuilder(SettingsService::class)
            ->setMethods(['getSettings'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockSettingsService->expects($this->once())->method('getSettings')->will($this->returnValue($settings));
        $this->inject($this->validator, 'settingsService', $mockSettingsService);

        $mockLocalizationService = $this->getMockBuilder(LocalizationService::class)
            ->setMethods(['translate'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockLocalizationService->expects($this->any())->method('translate')->will($this->returnValue(''));
        $this->inject($this->validator, 'localizationService', $mockLocalizationService);

        $this->assertEquals($expected, $this->validator->validate($changePassword)->hasErrors());
    }

    /**
     * @test
     */
    public function noCurrentPasswordGivenTest()
    {
        $changePassword = new ChangePassword();
        $changePassword->setCurrentPassword('');

        $settings = [
            'requireCurrentPassword' => [
                'enabled' => 1
            ]
        ];

        $mockSettingsService = $this->getMockBuilder(SettingsService::class)
            ->setMethods(['getSettings'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockSettingsService->expects($this->once())->method('getSettings')->will($this->returnValue($settings));
        $this->inject($this->validator, 'settingsService', $mockSettingsService);

        $mockLocalizationService = $this->getMockBuilder(LocalizationService::class)
            ->setMethods(['translate'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockLocalizationService->expects($this->any())->method('translate')->will($this->returnValue(''));
        $this->inject($this->validator, 'localizationService', $mockLocalizationService);

        $this->assertEquals(1570880411334, $this->validator->validate($changePassword)->getErrors()[0]->getCode());
    }

    /**
     * @test
     */
    public function currentPasswordWrongTest()
    {
        $changePassword = new ChangePassword();
        $changePassword->setCurrentPassword('invalid');

        $settings = [
            'requireCurrentPassword' => [
                'enabled' => 1
            ]
        ];

        $mockSettingsService = $this->getMockBuilder(SettingsService::class)
            ->setMethods(['getSettings'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockSettingsService->expects($this->once())->method('getSettings')->will($this->returnValue($settings));
        $this->inject($this->validator, 'settingsService', $mockSettingsService);

        $mockLocalizationService = $this->getMockBuilder(LocalizationService::class)
            ->setMethods(['translate'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockLocalizationService->expects($this->any())->method('translate')->will($this->returnValue(''));
        $this->inject($this->validator, 'localizationService', $mockLocalizationService);

        $mockOldPasswordService = $this->getMockBuilder(OldPasswordService::class)
            ->setMethods(['checkEqualsOldPassword'])
            ->disableOriginalConstructor()
            ->getMock();
        $mockOldPasswordService->expects($this->any())->method('checkEqualsOldPassword')
            ->will($this->returnValue(false));
        $this->inject($this->validator, 'oldPasswordService', $mockOldPasswordService);

        $this->assertEquals(1570880417020, $this->validator->validate($changePassword)->getErrors()[0]->getCode());
    }
}
