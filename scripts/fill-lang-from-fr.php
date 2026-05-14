<?php

declare(strict_types=1);

$base = dirname(__DIR__).'/resources/lang';
$fr = json_decode(file_get_contents($base.'/fr.json'), true);
if (! is_array($fr)) {
    fwrite(STDERR, "Invalid fr.json\n");
    exit(1);
}

$enPath = $base.'/en.json';
$arPath = $base.'/ar.json';
$en = json_decode(file_get_contents($enPath), true) ?: [];
$ar = json_decode(file_get_contents($arPath), true) ?: [];

$englishOverrides = [
    'admin.association' => 'Association',
    'admin.question_content' => 'Question wording',
    'admin.configuration' => 'Configuration',
    'admin.test_properties' => 'Test properties',
    'admin.describe_skills' => 'Describe the skills assessed…',
    'admin.offer_info' => 'Offer details',
    'admin.associated_test' => 'Linked assessment test',
    'admin.select_test' => 'Select a test',
    'admin.choose_test' => 'Choose a test',
    'admin.publish' => 'Published',
    'admin.contract_type' => 'Contract type',
    'admin.none' => 'None',
    'admin.applications' => 'Applications',
    'admin.content' => 'Content',
    'admin.rating' => 'Rating',
    'admin.published' => 'Published',
    'admin.type' => 'Type',
    'admin.tests' => 'Tests',
    'admin.no_test' => 'No test',
    'admin.level_hint' => 'Step for candidates: questions with the same level are shown together.',
    'admin.press_enter' => 'Press Enter after each option.',
    'admin.add_answer' => 'Add an answer',
    'admin.possible_answers' => 'Possible answers',
    'admin.answer_type' => 'Answer type',
    'admin.radio' => 'Multiple choice (radio)',
    'admin.list' => 'Dropdown',
    'admin.free_text' => 'Free text',
    'admin.date' => 'Date',
    'admin.photo' => 'Photo / file',
    'admin.primary' => 'Primary',
    'admin.secondary_class' => 'Secondary',
    'admin.mandatory' => 'Required',
    'admin.scored' => 'Scored',
    'admin.auto_correction' => 'Auto grading',
    'admin.correct_answer_select' => 'Correct answer',
    'admin.correct_answer_text' => 'Expected answer (text)',
    'admin.max_score' => 'Max score',
    'admin.second_ratio' => 'Secondary ratio',
    'admin.candidate_note' => 'Note for candidate',
    'admin.scoring_rule' => 'Scoring rule',
    'admin.question' => 'Question',
    'admin.question_fr' => 'Question (French)',
    'admin.question_en' => 'Question (English)',
    'admin.question_ar' => 'Question (Arabic)',
    'admin.group' => 'Group',
    'admin.groups' => 'Groups',
    'admin.group_name' => 'Group name',
    'admin.order' => 'Order',
    'admin.delete' => 'Delete',
    'admin.associated_offer' => 'Linked offer',
    'admin.no_offer' => 'No offer',
    'admin.test_name' => 'Test name',
    'admin.description' => 'Description',
    'admin.title' => 'Title',
    'admin.domain' => 'Domain',
    'admin.location' => 'Location',
    'admin.deadline' => 'Deadline',
    'admin.eligibility_threshold' => 'Eligibility threshold (%)',
    'admin.talent_threshold' => 'Talent threshold (%)',
    'admin.classification' => 'Classification',
    'admin.personal_info' => 'Personal information',
    'admin.status_scores' => 'Status & scores',
    'admin.primary_score' => 'Primary score',
    'admin.secondary_score' => 'Secondary score',
    'admin.phone' => 'Phone',
    'admin.full_name' => 'Full name',
    'admin.view_cv' => 'View CV',
    'admin.approve' => 'Approve',
    'admin.reject' => 'Reject',
    'admin.profile_approved' => 'Profile approved',
    'admin.profile_approved_msg' => 'Your candidate profile has been approved by an administrator.',
    'admin.approved_notif' => 'Candidate approved + notification sent',
    'admin.profile_rejected' => 'Profile not selected',
    'admin.profile_rejected_msg' => 'Your candidate profile was not retained at this stage.',
    'admin.rejected_notif' => 'Candidate rejected + notification sent',
    'admin.notifications_inbox' => 'Admin notifications',
    'admin.notifications_empty' => 'No notifications yet.',
    'admin.notifications_empty_hint' => 'When a candidate applies to an offer or submits a free application, you will see it here.',
    'admin.review_application' => 'Review application',
    'admin.notif_new_application_title' => 'New application',
    'admin.notif_new_application_message' => ':candidate (:email) applied for :offer.',
    'admin.application_section_info' => 'Details',
    'admin.application_cv_section' => 'CV review',
    'admin.application_cv_section_hint' => 'Open the CV, then accept to let the candidate take the assigned test, or refuse.',
    'admin.application_cv' => 'Curriculum vitae',
    'admin.application_no_cv' => 'No CV file is linked to this application.',
    'admin.application_view_cv' => 'View CV',
    'admin.application_view_cv_open' => 'Open CV (PDF)',
    'admin.application_associated_test' => 'Assessment test',
    'admin.application_associated_test_hint' => 'Required before accepting a free application. For an offer, this is usually prefilled from the offer.',
    'admin.application_accept_cv' => 'Accept (start test)',
    'admin.application_accept_cv_heading' => 'Accept this application after reviewing the CV?',
    'admin.application_accept_cv_description' => 'The candidate will be able to take the assessment test associated with this application.',
    'admin.application_accept_needs_test' => 'Choose an assessment test in the form before accepting.',
    'admin.application_toast_cv_accepted' => 'Application accepted — the candidate can take the test.',
    'admin.application_reject_cv' => 'Refuse',
    'admin.application_reject_cv_heading' => 'Refuse this application?',
    'admin.application_reject_cv_description' => 'The candidate will be notified that their application was not selected.',
    'admin.application_toggle_apply_enabled' => 'Application enabled',
    'admin.application_column_applicant' => 'Applicant',
    'admin.application_column_offer' => 'Job offer',
    'admin.application_column_level_short' => 'Lvl.',
    'admin.application_action_validate_level' => 'Validate level',
    'admin.application_action_validate_level_heading' => 'Validate this level and move to the next?',
    'admin.application_action_validate_level_description' => 'The candidate will be notified and can answer questions for the next level.',
    'admin.application_toast_level_advanced' => 'Level :old validated — level :new unlocked',
    'admin.application_action_validate_final' => 'Approve application',
    'admin.application_action_validate_final_heading' => 'Fully approve this application?',
    'admin.application_toast_application_validated' => 'Application approved — candidate notified',
    'admin.application_action_reject' => 'Reject',
    'admin.application_action_reject_heading' => 'Reject this application?',
    'admin.application_toast_rejected' => 'Application rejected — candidate notified',
    'admin.application_action_publish_score' => 'Publish score',
    'admin.application_toast_score_published' => 'Score published — candidate notified',
    'admin.application_action_archive' => 'Archive selection',
    'admin.application_action_archive_heading' => 'Archive the selected applications?',
];

function humanizeEnglish(string $key): string
{
    $prefixes = ['admin.', 'nav.', 'test.', 'test_builder.', 'question_form.', 'temoignage.', 'verify.', 'register.'];
    foreach ($prefixes as $p) {
        if (str_starts_with($key, $p)) {
            $key = substr($key, strlen($p));
            break;
        }
    }
    $key = str_replace(['-', '_'], ' ', $key);

    return mb_convert_case($key, MB_CASE_TITLE, 'UTF-8');
}

foreach ($fr as $key => $frValue) {
    if (! array_key_exists($key, $en) || $en[$key] === '' || $en[$key] === null) {
        $en[$key] = $englishOverrides[$key] ?? humanizeEnglish((string) $key);
    }
}

foreach (array_keys($fr) as $key) {
    if (! array_key_exists($key, $ar) || $ar[$key] === '' || $ar[$key] === null) {
        $ar[$key] = $en[$key] ?? humanizeEnglish((string) $key);
    }
}

ksort($en);
ksort($ar);

file_put_contents($enPath, json_encode($en, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");
file_put_contents($arPath, json_encode($ar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n");

fwrite(STDOUT, 'en keys: '.count($en).PHP_EOL);
fwrite(STDOUT, 'ar keys: '.count($ar).PHP_EOL);
