<?php 

if ( !defined('ABSPATH') ) {
    exit;
}

if ( !class_exists('PT_Defaults_Values') ) {
    class PT_Defaults_Values {

        public static function get_title_options() {
            return array(
                'Administrator' => 'Administrator',
                'Assistant Administrator' => 'Assistant Administrator',
                'Admissions' => 'Admissions',
                'Assistant Director of Nursing' => 'Assistant Director of Nursing',
                'Activities' => 'Activities',
                'Business Office' => 'Business Office',
                'CNA' => 'CNA',
                'Corporate/Executive' => 'Corporate/Executive',
                'Corporate Clinical' => 'Corporate Clinical',
                'Corporate Operations' => 'Corporate Operations',
                'Corporate Finance' => 'Corporate Finance',
                'Director of Nursing' => 'Director of Nursing',
                'Dietician/Dietary Services' => 'Dietician/Dietary Services',
                'Environmental Services' => 'Environmental Services',
                'Finance' => 'Finance',
                'Human Resources' => 'Human Resources',
                'Infection Control' => 'Infection Control',
                'LPN/LVN' => 'LPN/LVN',
                'Maintenance' => 'Maintenance',
                'MDS' => 'MDS',
				'Medical Records' => 'Medical Records',
                'Physician/Practitioner' => 'Physician/Practitioner',
                'RN' => 'RN',
                'Quality Assurance' => 'Quality Assurance',
				'Safety and Dementia' => 'Safety and Dementia',
                'Staff Development' => 'Staff Development',
                'Social Services' => 'Social Services',
                'Staff Coordinator' => 'Staff Coordinator',
                'Therapist/Rehab Director' => 'Therapist/Rehab Director',
                'Resident Care Coordinator/Case Manager' => 'Resident Care Coordinator/Case Manager',
            );
        }
        
        public static function get_user_status_options() {
            return array(
                'disable'   => 'Disable User Status',
                'enable'    => 'Enable User Status (default)',
            );
        }
        public static function get_user_tnt_options() {
            return array(
                'disable'   => 'Disable Tools & Templates Access',
                'enable'    => 'Enable Tools & Templates Access (default)',
            );
        }
        public static function get_user_pnp_status() {
            return array(
                'disable'   => 'Disable Policies & Procedures Access',
                'enable'    => 'Enable Policies & Procedures Access (default)',
            );
        }
        public static function get_user_documents_upload_permission_status() {
            return array(
                'disable'   => 'Disable Frontend Documents Upload Access (default)',
                'enable'    => 'Enable Frontend Documents Upload Access',
            );
        }
        
        public static function get_usa_states_options() {
            return array(
                'AL' => 'Alabama',
                'AK' => 'Alaska',
                'AZ' => 'Arizona',
                'AR' => 'Arkansas',
                'CA' => 'California',
                'CO' => 'Colorado',
                'CT' => 'Connecticut',
                'DE' => 'Delaware',
                'DC' => 'District of Columbia',
                'FL' => 'Florida',
                'GA' => 'Georgia',
                'HI' => 'Hawaii',
                'ID' => 'Idaho',
                'IL' => 'Illinois',
                'IN' => 'Indiana',
                'IA' => 'Iowa',
                'KS' => 'Kansas',
                'KY' => 'Kentucky',
                'LA' => 'Louisiana',
                'ME' => 'Maine',
                'MD' => 'Maryland',
                'MA' => 'Massachusetts',
                'MI' => 'Michigan',
                'MN' => 'Minnesota',
                'MS' => 'Mississippi',
                'MO' => 'Missouri',
                'MT' => 'Montana',
                'NE' => 'Nebraska',
                'NV' => 'Nevada',
                'NH' => 'New Hampshire',
                'NJ' => 'New Jersey',
                'NM' => 'New Mexico',
                'NY' => 'New York',
                'NC' => 'North Carolina',
                'ND' => 'North Dakota',
                'OH' => 'Ohio',
                'OK' => 'Oklahoma',
                'OR' => 'Oregon',
                'PA' => 'Pennsylvania',
                'RI' => 'Rhode Island',
                'SC' => 'South Carolina',
                'SD' => 'South Dakota',
                'TN' => 'Tennessee',
                'TX' => 'Texas',
                'UT' => 'Utah',
                'VT' => 'Vermont',
                'VA' => 'Virginia',
                'WA' => 'Washington',
                'WV' => 'West Virginia',
                'WI' => 'Wisconsin',
                'WY' => 'Wyoming',
            );
        } 
        
        public static function  get_account_manager_users_list() {
            return get_users(array(
                'role'         => 'account_manager',
                'fields' => array( 'user_email','ID' ),
                'meta_key'     => 'user_status',
                'meta_value'   => 'enable',
                'meta_compare' => '=',
            ));
        }
        
        public static function  get_security_questions() {
            return array(
                'question-1' => 'What was your favorite place to visit as a child?',
                'question-2' => 'Who is your favorite actor, musician, or artist?',
                'question-3' => 'What is the name of your favorite pet?',
                'question-4' => 'In what city were you born?',
                'question-5' => 'What high school did you attend?',
                'question-6' => 'What is your mother\'s maiden name?',
                'question-7' => 'What is your father\'s middle name?',
                'question-8' => 'What is your favorite color?',
                'question-9' => 'What is the name of your first grade teacher?',
                'question-10' => 'What is the first name of your first boyfriend or girlfriend?',
            );
        }
    }
}