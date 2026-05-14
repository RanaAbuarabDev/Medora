<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property-read \App\Models\Laboratory|null $lab
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabSchedule newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabSchedule newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabSchedule query()
 * @mixin \Eloquent
 */
	class LabSchedule extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $lab_id
 * @property int $master_test_id
 * @property numeric $price
 * @property int $is_available
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest whereIsAvailable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest whereLabId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest whereMasterTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LabTest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class LabTest extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $logo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $manager
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MasterTest> $masterTests
 * @property-read int|null $master_tests_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $staff
 * @property-read int|null $staff_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Laboratory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Laboratory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $test_category_id
 * @property string $name
 * @property string|null $short_name
 * @property string|null $sample_type
 * @property string|null $unit
 * @property string|null $normal_range
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\TestCategory $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Laboratory> $laboratories
 * @property-read int|null $laboratories_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereNormalRange($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereSampleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereShortName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereTestCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterTest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class MasterTest extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $icon
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MasterTest> $masterTests
 * @property-read int|null $master_tests_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestCategory whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TestCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class TestCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $lab_id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Laboratory|null $laboratory
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLabId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 * @mixin \Eloquent
 */
	class User extends \Eloquent {}
}

