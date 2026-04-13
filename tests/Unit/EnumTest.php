<?php

namespace Tests\Unit;

use App\Enums\AuthRouteNames;
use App\Enums\DesignationStatus;
use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Enums\ServiceResultType;
use App\Enums\TaskStatus;
use App\Enums\TokenAbility;
use App\Enums\TopicStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    #[DataProvider('labelProvider')]
    public function test_enum_labels_are_mapped_correctly(object $case, string $expected): void
    {
        $this->assertSame($expected, $case->getLabel());
    }

    public static function labelProvider(): array
    {
        return [
            [DesignationStatus::ACTIVE, 'Active'],
            [DesignationStatus::INACTIVE, 'Inactive'],
            [PostStatus::DRAFT, 'Draft'],
            [PostStatus::PENDING, 'Pending'],
            [PostStatus::APPROVED, 'Approved'],
            [PostStatus::DECLINED, 'Declined'],
            [PostStatus::INACTIVE, 'Inactive'],
            [PostType::BLOG, 'Blog'],
            [PostType::FLASHCARD, 'Flashcard'],
            [PostType::TWEET, 'Tweet'],
            [ServiceResultType::DATA, 'data'],
            [ServiceResultType::META, 'meta'],
            [ServiceResultType::JSON, 'json'],
            [ServiceResultType::DELETE, 'delete'],
            [ServiceResultType::ERROR, 'error'],
            [TaskStatus::PENDING, 'Pending'],
            [TaskStatus::SUBMITTED, 'Submitted'],
            [TaskStatus::SCHEDULED, 'Scheduled'],
            [TaskStatus::PUBLISHED, 'Published'],
            [TaskStatus::APPROVED, 'Approved'],
            [TaskStatus::DECLINED, 'Declined'],
            [TopicStatus::ACTIVE, 'Active'],
            [TopicStatus::INACTIVE, 'Inactive'],
            [UserRole::ADMIN, 'Admin'],
            [UserRole::USER, 'User'],
            [UserStatus::ACTIVE, 'Active'],
            [UserStatus::INACTIVE, 'Inactive'],
        ];
    }

    #[DataProvider('authRouteResourceTypeProvider')]
    public function test_auth_route_names_map_to_expected_resource_types(AuthRouteNames $routeName, string $expected): void
    {
        $this->assertSame($expected, $routeName->getResourceType());
    }

    public static function authRouteResourceTypeProvider(): array
    {
        return [
            [AuthRouteNames::REGISTER, 'register'],
            [AuthRouteNames::SET_PASSWORD, 'set-password'],
            [AuthRouteNames::LOGIN, 'token'],
            [AuthRouteNames::REFRESH_TOKEN, 'refresh-token'],
            [AuthRouteNames::FORGOT_PASSWORD, 'reset-password'],
            [AuthRouteNames::RESET_PASSWORD, 'reset-password'],
            [AuthRouteNames::VERIFY_EMAIL, 'verify-email'],
            [AuthRouteNames::EMAIL_VERIFY_NOTIFICATION, 'verify-email'],
            [AuthRouteNames::LOGOUT, 'logout'],
        ];
    }

    public function test_token_ability_values_remain_stable(): void
    {
        $this->assertSame('issue-access-token', TokenAbility::ISSUE_ACCESS_TOKEN->value);
        $this->assertSame('access-api', TokenAbility::ACCESS_API->value);
    }
}
