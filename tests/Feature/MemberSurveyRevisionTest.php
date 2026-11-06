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
        (require database_path('migrations/2026_11_06_000001_add_edm_received_to_dmc_member_surveys.php'))->up();
    }

    private function input(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Test Member', 'company' => 'Test Company', 'email' => 'member@example.com',
            'event_types' => ['Other'], 'event_types_other' => 'Site visits',
            'nominee_reason' => 'Industry leadership',
            'event_improvement_aspects' => ['Speakers & Presentation Quality', 'Other'],
            'event_improvement_other' => 'Accessibility',
            'edm_received' => ['Yes'], 'platforms' => ['WhatsApp Channel'],
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
        $this->assertSame('Yes', $survey->edm_received);
        $this->assertSame('Site visits', $survey->event_types_other);
        $this->assertSame('Industry leadership', $survey->nominee_reason);
        $this->assertSame(['Speakers & Presentation Quality', 'Other'], $survey->event_improvement_aspects);
        $this->assertSame('Accessibility', $survey->event_improvement_other);
        $this->assertSame(['WhatsApp Channel'], $survey->platforms);
        $this->assertSame(['2 emails per week'], $survey->email_frequency);
    }

    public function test_invalid_revised_options_are_rejected(): void
    {
        $this->expectException(ValidationException::class);
        (new DmcMemberSurveyController)->store(Request::create('/survey-member', 'POST', $this->input(['email_frequency' => ['Every day']])));
    }

    public function test_new_program_choices_and_edm_answer_are_saved(): void
    {
        $mock = \Mockery::mock('overload:App\\Helpers\\WhatsappApi');
        $mock->shouldReceive('WhatsappMessageGroup')->once();
        (new DmcMemberSurveyController)->store(Request::create('/survey-member', 'POST', $this->input([
            'event_types' => ['Conference/ Panel Discussion/ Expert Talk', 'Sport Activites/ Festive Celebrations'],
            'event_types_other' => null,
            'event_improvement_aspects' => ['Program Topics & Content', 'Program Format & Duration'],
            'event_improvement_other' => null,
            'edm_received' => ['No'],
        ])));
        $survey = DmcMemberSurvey::firstOrFail();
        $this->assertSame('No', $survey->edm_received);
        $this->assertSame(['Program Topics & Content', 'Program Format & Duration'], $survey->event_improvement_aspects);
    }

    public function test_all_other_answers_require_a_description(): void
    {
        foreach (['event_types_other', 'event_improvement_other', 'email_primary_goal_other'] as $field) {
            $input = $this->input(['email_primary_goal' => ['Other'], 'email_primary_goal_other' => 'Something else']);
            $input[$field] = '   ';
            try {
                (new DmcMemberSurveyController)->store(Request::create('/survey-member', 'POST', $input));
                $this->fail("Missing description was accepted: {$field}");
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey($field, $exception->errors());
            }
        }
    }

    public function test_none_option_cannot_be_combined_with_platforms(): void
    {
        try {
            (new DmcMemberSurveyController)->store(Request::create('/survey-member', 'POST', $this->input([
                'platforms' => ["I don't follow any", 'LinkedIn'],
            ])));
            $this->fail('Conflicting platform choices were accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('platforms', $exception->errors());
        }
    }

    public function test_none_option_is_saved_and_other_descriptions_are_optional_when_not_selected(): void
    {
        $mock = \Mockery::mock('overload:App\\Helpers\\WhatsappApi');
        $mock->shouldReceive('WhatsappMessageGroup')->once();
        (new DmcMemberSurveyController)->store(Request::create('/survey-member', 'POST', $this->input([
            'event_types' => ['Seminar/ Workshops/ Training Session'], 'event_types_other' => null,
            'event_improvement_aspects' => [], 'event_improvement_other' => null,
            'email_primary_goal' => [], 'email_primary_goal_other' => null,
            'platforms' => ["I don't follow any"],
        ])));
        $this->assertSame(["I don't follow any"], DmcMemberSurvey::firstOrFail()->platforms);
    }

    protected function tearDown(): void
    {
        DB::purge('survey_test');
        parent::tearDown();
    }
}
