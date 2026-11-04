<?php

namespace Tests\Feature;

use App\Http\Controllers\DmcMemberSurveyController;
use App\Models\DmcMemberSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MemberSurveyRevisionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'survey_test', 'database.connections.survey_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        require_once database_path('migrations/2025_12_15_133945_create_dmc_member_surveys_table.php');
        require_once database_path('migrations/2025_12_15_152622_add_email_preference_fields_to_dmc_member_surveys_table.php');
        require_once database_path('migrations/2026_11_04_000000_add_revision_fields_to_dmc_member_surveys.php');
        (new \CreateDmcMemberSurveysTable)->up();
        (new \AddEmailPreferenceFieldsToDmcMemberSurveysTable)->up();
        (new \AddRevisionFieldsToDmcMemberSurveys)->up();
    }

    private function input(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Test Member', 'company' => 'Test Company', 'email' => 'member@example.com',
            'event_types' => ['Other'], 'event_types_other' => 'Site visits',
            'nominee_reason' => 'Industry leadership',
            'event_improvement_aspects' => ['Speakers & Presentations', 'Other'],
            'event_improvement_other' => 'Accessibility',
            'social_familiarity' => ['Very familiar'], 'platforms' => ['WhatsApp Channel'],
            'app_awareness' => ['Yes, both'], 'email_frequency' => ['2 emails per week'],
        ], $overrides);
    }

    public function test_revised_answers_are_validated_and_saved(): void
    {
        $mock = \Mockery::mock('overload:App\\Helpers\\WhatsappApi');
        $mock->shouldReceive('WhatsappMessageGroup')->once();
        $request = Request::create('/survey-member', 'POST', $this->input());
        (new DmcMemberSurveyController)->store($request);
        $survey = DmcMemberSurvey::firstOrFail();
        $this->assertSame('Site visits', $survey->event_types_other);
        $this->assertSame('Industry leadership', $survey->nominee_reason);
        $this->assertSame(['Speakers & Presentations', 'Other'], $survey->event_improvement_aspects);
        $this->assertSame('Accessibility', $survey->event_improvement_other);
        $this->assertSame(['WhatsApp Channel'], $survey->platforms);
        $this->assertSame(['2 emails per week'], $survey->email_frequency);
    }

    public function test_invalid_revised_options_are_rejected(): void
    {
        $this->expectException(ValidationException::class);
        (new DmcMemberSurveyController)->store(Request::create('/survey-member', 'POST', $this->input(['email_frequency' => ['Every day']])));
    }

    protected function tearDown(): void
    {
        DB::purge('survey_test');
        parent::tearDown();
    }
}
