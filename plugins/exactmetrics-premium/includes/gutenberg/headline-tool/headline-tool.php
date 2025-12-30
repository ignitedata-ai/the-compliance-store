<?php
// phpcs:ignoreFile
namespace ExactMetricsHeadlineToolPlugin;

// setup defines
define( 'EXACTMETRICS_HEADLINE_TOOL_DIR_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Headline Tool
 *
 * @since      0.1
 * @author     Debjit Saha
 */
class ExactMetricsHeadlineToolPlugin {

	/**
	 * Class Variables.
	 */
	private $emotion_power_words = array();
	private $power_words = array();
	private $common_words = array();
	private $uncommon_words = array();

	/**
	 * Constructor
	 *
	 * @return   none
	 */
	function __construct() {
		$this->init();
	}

	/**
	 * Add the necessary hooks and filters
	 */
	function init() {
		add_action( 'wp_ajax_exactmetrics_gutenberg_headline_analyzer_get_results', array( $this, 'get_result' ) );
	}

	/**
	 * Ajax request endpoint for the uptime check
	 */
	function get_result() {

		// csrf check
		if ( check_ajax_referer( 'exactmetrics_gutenberg_headline_nonce', false, false ) === false ) {
			$content = self::output_template( 'results-error.php' );
			wp_send_json_error(
				array(
					'html' => $content
				)
			);
		}

		// get whether or not the website is up
		$result = $this->get_headline_scores();

		if ( ! empty( $result->err ) ) {
			$content = self::output_template( 'results-error.php', $result );
			wp_send_json_error(
				array( 'html' => $content, 'analysed' => false )
			);
		} else {
			if(!isset($_REQUEST['q'])){
				wp_send_json_error(
					array( 'html' => '', 'analysed' => false )
				);
			}
			$q = (isset($_REQUEST['q'])) ? sanitize_text_field($_REQUEST['q']) : '';
			// send the response
			wp_send_json_success(
				array(
					'result'   => $result,
					'analysed' => ! $result->err,
					'sentence' => ucwords( wp_unslash( $q ) ),
					'score'    => ( isset( $result->score ) && ! empty( $result->score ) ) ? $result->score : 0
				)
			);

		}
	}

	/**
	 * function to match words from sentence
	 * @return Object
	 */
	function match_words( $sentence, $sentence_split, $words ) {
		$ret = array();
		foreach ( $words as $wrd ) {
			// check if $wrd is a phrase
			if ( strpos( $wrd, ' ' ) !== false ) {
				$word_position = strpos( $sentence, $wrd );

				// Word not found in the sentence.
				if ( $word_position === false ) {
					continue;
				}

				// Check this is the end of the sentence.
				$is_end = strlen( $sentence ) === $word_position + 1;

				// Check the next character is a space.
				$is_space = " " === substr( $sentence, $word_position + strlen( $wrd ), 1 );

				// If it is a phrase then the next character must end of sentence or a space.
				if ( $is_end || $is_space ) {
					$ret[] = $wrd;
				}
			} // if $wrd is a single word
			else {
				if ( in_array( $wrd, $sentence_split ) ) {
					$ret[] = $wrd;
				}
			}
		}

		return $ret;
	}

	/**
	 * main function to calculate headline scores
	 * @return Object
	 */
	function get_headline_scores() {
		$input = (isset($_REQUEST['q'])) ? sanitize_text_field($_REQUEST['q']) : '';

		// init the result array
		$result                   = new \stdClass();
		$result->input_array_orig = explode( ' ', wp_unslash( $input ) );

		// strip useless characters
		$input = preg_replace( '/[^A-Za-z0-9 ]/', '', $input );

		// strip whitespace
		$input = preg_replace( '!\s+!', ' ', $input );

		// lower case
		$input = strtolower( $input );

		$result->input = $input;

		// bad input
		if ( ! $input || $input == ' ' || trim( $input ) == '' ) {
			$result->err = true;
			$result->msg = __( 'Bad Input', 'exactmetrics-premium' );

			return $result;
		}

		// overall score;
		$scoret = 0;

		// headline array
		$input_array = explode( ' ', $input );

		$result->input_array = $input_array;

		// all okay, start analysis
		$result->err = false;

		// Length - 55 chars. optimal
		$result->length = strlen( str_replace( ' ', '', $input ) );
		$scoret         = $scoret + 3;

		if ( $result->length <= 19 ) {
			$scoret += 5;
		} elseif ( $result->length >= 20 && $result->length <= 34 ) {
			$scoret += 8;
		} elseif ( $result->length >= 35 && $result->length <= 66 ) {
			$scoret += 11;
		} elseif ( $result->length >= 67 && $result->length <= 79 ) {
			$scoret += 8;
		} elseif ( $result->length >= 80 ) {
			$scoret += 5;
		}

		// Count - typically 6-7 words
		$result->word_count = count( $input_array );
		$scoret             = $scoret + 3;

		if ( $result->word_count == 0 ) {
			$scoret = 0;
		} else if ( $result->word_count >= 2 && $result->word_count <= 4 ) {
			$scoret += 5;
		} elseif ( $result->word_count >= 5 && $result->word_count <= 9 ) {
			$scoret += 11;
		} elseif ( $result->word_count >= 10 && $result->word_count <= 11 ) {
			$scoret += 8;
		} elseif ( $result->word_count >= 12 ) {
			$scoret += 5;
		}

		// Calculate word match counts
		$result->power_words        = $this->match_words( $result->input, $result->input_array, $this->power_words() );
		$result->power_words_per    = count( $result->power_words ) / $result->word_count;
		$result->emotion_words      = $this->match_words( $result->input, $result->input_array, $this->emotion_power_words() );
		$result->emotion_words_per  = count( $result->emotion_words ) / $result->word_count;
		$result->common_words       = $this->match_words( $result->input, $result->input_array, $this->common_words() );
		$result->common_words_per   = count( $result->common_words ) / $result->word_count;
		$result->uncommon_words     = $this->match_words( $result->input, $result->input_array, $this->uncommon_words() );
		$result->uncommon_words_per = count( $result->uncommon_words ) / $result->word_count;
		$result->word_balance       = __( 'Can Be Improved', 'exactmetrics-premium' );
		$result->word_balance_use   = array();

		if ( $result->emotion_words_per < 0.1 ) {
			$result->word_balance_use[] = __( 'emotion', 'exactmetrics-premium' );
		} else {
			$scoret = $scoret + 15;
		}

		if ( $result->common_words_per < 0.2 ) {
			$result->word_balance_use[] = __( 'common', 'exactmetrics-premium' );
		} else {
			$scoret = $scoret + 11;
		}

		if ( $result->uncommon_words_per < 0.1 ) {
			$result->word_balance_use[] = __( 'uncommon', 'exactmetrics-premium' );
		} else {
			$scoret = $scoret + 15;
		}

		if ( count( $result->power_words ) < 1 ) {
			$result->word_balance_use[] = __( 'power', 'exactmetrics-premium' );
		} else {
			$scoret = $scoret + 19;
		}

		if (
			$result->emotion_words_per >= 0.1 &&
			$result->common_words_per >= 0.2 &&
			$result->uncommon_words_per >= 0.1 &&
			count( $result->power_words ) >= 1 ) {
			$result->word_balance = __( 'Perfect', 'exactmetrics-premium' );
			$scoret               = $scoret + 3;
		}

		// Sentiment analysis also look - https://github.com/yooper/php-text-analysis

		// Emotion of the headline - sentiment analysis
		// Credits - https://github.com/JWHennessey/phpInsight/
		require_once EXACTMETRICS_HEADLINE_TOOL_DIR_PATH . '/phpinsight/autoload.php';
		$sentiment         = new \PHPInsight\Sentiment();
		$class_senti       = $sentiment->categorise( $input );
		$result->sentiment = $class_senti;

		$scoret = $scoret + ( $result->sentiment === 'pos' ? 10 : ( $result->sentiment === 'neg' ? 10 : 7 ) );

		// Headline types
		$headline_types = array();

		// HDL type: how to, how-to, howto
		if ( strpos( $input, __( 'how to', 'exactmetrics-premium' ) ) !== false || strpos( $input, __( 'howto', 'exactmetrics-premium' ) ) !== false ) {
			$headline_types[] = __( 'How-To', 'exactmetrics-premium' );
			$scoret           = $scoret + 7;
		}

		// HDL type: numbers - numeric and alpha
		$num_quantifiers = array(
			__( 'one', 'exactmetrics-premium' ),
			__( 'two', 'exactmetrics-premium' ),
			__( 'three', 'exactmetrics-premium' ),
			__( 'four', 'exactmetrics-premium' ),
			__( 'five', 'exactmetrics-premium' ),
			__( 'six', 'exactmetrics-premium' ),
			__( 'seven', 'exactmetrics-premium' ),
			__( 'eight', 'exactmetrics-premium' ),
			__( 'nine', 'exactmetrics-premium' ),
			__( 'eleven', 'exactmetrics-premium' ),
			__( 'twelve', 'exactmetrics-premium' ),
			__( 'thirt', 'exactmetrics-premium' ),
			__( 'fift', 'exactmetrics-premium' ),
			__( 'hundred', 'exactmetrics-premium' ),
			__( 'thousand', 'exactmetrics-premium' ),
		);

		$list_words = array_intersect( $input_array, $num_quantifiers );
		if ( preg_match( '~[0-9]+~', $input ) || ! empty ( $list_words ) ) {
			$headline_types[] = __( 'List', 'exactmetrics-premium' );
			$scoret           = $scoret + 7;
		}

		// HDL type: Question
		$qn_quantifiers     = array(
			__( 'where', 'exactmetrics-premium' ),
			__( 'when', 'exactmetrics-premium' ),
			__( 'how', 'exactmetrics-premium' ),
			__( 'what', 'exactmetrics-premium' ),
			__( 'have', 'exactmetrics-premium' ),
			__( 'has', 'exactmetrics-premium' ),
			__( 'does', 'exactmetrics-premium' ),
			__( 'do', 'exactmetrics-premium' ),
			__( 'can', 'exactmetrics-premium' ),
			__( 'are', 'exactmetrics-premium' ),
			__( 'will', 'exactmetrics-premium' ),
		);
		$qn_quantifiers_sub = array(
			__( 'you', 'exactmetrics-premium' ),
			__( 'they', 'exactmetrics-premium' ),
			__( 'he', 'exactmetrics-premium' ),
			__( 'she', 'exactmetrics-premium' ),
			__( 'your', 'exactmetrics-premium' ),
			__( 'it', 'exactmetrics-premium' ),
			__( 'they', 'exactmetrics-premium' ),
			__( 'my', 'exactmetrics-premium' ),
			__( 'have', 'exactmetrics-premium' ),
			__( 'has', 'exactmetrics-premium' ),
			__( 'does', 'exactmetrics-premium' ),
			__( 'do', 'exactmetrics-premium' ),
			__( 'can', 'exactmetrics-premium' ),
			__( 'are', 'exactmetrics-premium' ),
			__( 'will', 'exactmetrics-premium' ),
		);
		if ( in_array( $input_array[0], $qn_quantifiers ) ) {
			if ( in_array( $input_array[1], $qn_quantifiers_sub ) ) {
				$headline_types[] = __( 'Question', 'exactmetrics-premium' );
				$scoret           = $scoret + 7;
			}
		}

		// General headline type
		if ( empty( $headline_types ) ) {
			$headline_types[] = __( 'General', 'exactmetrics-premium' );
			$scoret           = $scoret + 5;
		}

		// put to result
		$result->headline_types = $headline_types;

		// Resources for more reading:
		// https://kopywritingkourse.com/copywriting-headlines-that-sell/
		// How To _______ That Will Help You ______
		// https://coschedule.com/blog/how-to-write-the-best-headlines-that-will-increase-traffic/

		$result->score = $scoret >= 93 ? 93 : $scoret;

		return $result;
	}

	/**
	 * Output template contents
	 *
	 * @param $template String template file name
	 *
	 * @return String template content
	 */
	static function output_template( $template, $result = '', $theme = '' ) {
		ob_start();
		require EXACTMETRICS_HEADLINE_TOOL_DIR_PATH . '' . $template;
		$tmp = ob_get_contents();
		ob_end_clean();

		return $tmp;
	}

	/**
	 * Get User IP
	 *
	 * Returns the IP address of the current visitor
	 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/blob/904db487f6c07a3a46903202d31d4e8ea2b30808/includes/misc-functions.php#L163
	 * @return string $ip User's IP address
	 */
	static function get_ip() {

		$ip = '127.0.0.1';

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			//check ip from share internet
			$ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			//to check ip is pass from proxy
			$ip = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
		}

		// Fix potential CSV returned from $_SERVER variables
		$ip_array = explode( ',', $ip );
		$ip_array = array_map( 'trim', $ip_array );

		return $ip_array[0];
	}

	/**
	 * Emotional power words
	 *
	 * @return array emotional power words
	 */
	function emotion_power_words() {
		if ( isset( $this->emotion_power_words ) && ! empty( $this->emotion_power_words ) ) {
			return $this->emotion_power_words;
		}

		$this->emotion_power_words = array(
			__( "destroy", "exactmetrics-premium" ),
			__( "extra", "exactmetrics-premium" ),
			__( "in a", "exactmetrics-premium" ),
			__( "devastating", "exactmetrics-premium" ),
			__( "eye-opening", "exactmetrics-premium" ),
			__( "gift", "exactmetrics-premium" ),
			__( "in the world", "exactmetrics-premium" ),
			__( "devoted", "exactmetrics-premium" ),
			__( "fail", "exactmetrics-premium" ),
			__( "in the", "exactmetrics-premium" ),
			__( "faith", "exactmetrics-premium" ),
			__( "grateful", "exactmetrics-premium" ),
			__( "inexpensive", "exactmetrics-premium" ),
			__( "dirty", "exactmetrics-premium" ),
			__( "famous", "exactmetrics-premium" ),
			__( "disastrous", "exactmetrics-premium" ),
			__( "fantastic", "exactmetrics-premium" ),
			__( "greed", "exactmetrics-premium" ),
			__( "grit", "exactmetrics-premium" ),
			__( "insanely", "exactmetrics-premium" ),
			__( "disgusting", "exactmetrics-premium" ),
			__( "fearless", "exactmetrics-premium" ),
			__( "disinformation", "exactmetrics-premium" ),
			__( "feast", "exactmetrics-premium" ),
			__( "insidious", "exactmetrics-premium" ),
			__( "dollar", "exactmetrics-premium" ),
			__( "feeble", "exactmetrics-premium" ),
			__( "gullible", "exactmetrics-premium" ),
			__( "double", "exactmetrics-premium" ),
			__( "fire", "exactmetrics-premium" ),
			__( "hack", "exactmetrics-premium" ),
			__( "fleece", "exactmetrics-premium" ),
			__( "had enough", "exactmetrics-premium" ),
			__( "invasion", "exactmetrics-premium" ),
			__( "drowning", "exactmetrics-premium" ),
			__( "floundering", "exactmetrics-premium" ),
			__( "happy", "exactmetrics-premium" ),
			__( "ironclad", "exactmetrics-premium" ),
			__( "dumb", "exactmetrics-premium" ),
			__( "flush", "exactmetrics-premium" ),
			__( "hate", "exactmetrics-premium" ),
			__( "irresistibly", "exactmetrics-premium" ),
			__( "hazardous", "exactmetrics-premium" ),
			__( "is the", "exactmetrics-premium" ),
			__( "fool", "exactmetrics-premium" ),
			__( "is what happens when", "exactmetrics-premium" ),
			__( "fooled", "exactmetrics-premium" ),
			__( "helpless", "exactmetrics-premium" ),
			__( "it looks like a", "exactmetrics-premium" ),
			__( "embarrass", "exactmetrics-premium" ),
			__( "for the first time", "exactmetrics-premium" ),
			__( "help are the", "exactmetrics-premium" ),
			__( "jackpot", "exactmetrics-premium" ),
			__( "forbidden", "exactmetrics-premium" ),
			__( "hidden", "exactmetrics-premium" ),
			__( "jail", "exactmetrics-premium" ),
			__( "empower", "exactmetrics-premium" ),
			__( "force-fed", "exactmetrics-premium" ),
			__( "high", "exactmetrics-premium" ),
			__( "jaw-dropping", "exactmetrics-premium" ),
			__( "forgotten", "exactmetrics-premium" ),
			__( "jeopardy", "exactmetrics-premium" ),
			__( "energize", "exactmetrics-premium" ),
			__( "hoax", "exactmetrics-premium" ),
			__( "jubilant", "exactmetrics-premium" ),
			__( "foul", "exactmetrics-premium" ),
			__( "hope", "exactmetrics-premium" ),
			__( "killer", "exactmetrics-premium" ),
			__( "frantic", "exactmetrics-premium" ),
			__( "horrific", "exactmetrics-premium" ),
			__( "know it all", "exactmetrics-premium" ),
			__( "epic", "exactmetrics-premium" ),
			__( "how to make", "exactmetrics-premium" ),
			__( "evil", "exactmetrics-premium" ),
			__( "freebie", "exactmetrics-premium" ),
			__( "frenzy", "exactmetrics-premium" ),
			__( "hurricane", "exactmetrics-premium" ),
			__( "excited", "exactmetrics-premium" ),
			__( "fresh on the mind", "exactmetrics-premium" ),
			__( "frightening", "exactmetrics-premium" ),
			__( "hypnotic", "exactmetrics-premium" ),
			__( "lawsuit", "exactmetrics-premium" ),
			__( "frugal", "exactmetrics-premium" ),
			__( "illegal", "exactmetrics-premium" ),
			__( "fulfill", "exactmetrics-premium" ),
			__( "lick", "exactmetrics-premium" ),
			__( "explode", "exactmetrics-premium" ),
			__( "lies", "exactmetrics-premium" ),
			__( "exposed", "exactmetrics-premium" ),
			__( "gambling", "exactmetrics-premium" ),
			__( "like a normal", "exactmetrics-premium" ),
			__( "nightmare", "exactmetrics-premium" ),
			__( "results", "exactmetrics-premium" ),
			__( "line", "exactmetrics-premium" ),
			__( "no good", "exactmetrics-premium" ),
			__( "pound", "exactmetrics-premium" ),
			__( "loathsome", "exactmetrics-premium" ),
			__( "no questions asked", "exactmetrics-premium" ),
			__( "revenge", "exactmetrics-premium" ),
			__( "lonely", "exactmetrics-premium" ),
			__( "looks like a", "exactmetrics-premium" ),
			__( "obnoxious", "exactmetrics-premium" ),
			__( "preposterous", "exactmetrics-premium" ),
			__( "revolting", "exactmetrics-premium" ),
			__( "looming", "exactmetrics-premium" ),
			__( "priced", "exactmetrics-premium" ),
			__( "lost", "exactmetrics-premium" ),
			__( "prison", "exactmetrics-premium" ),
			__( "lowest", "exactmetrics-premium" ),
			__( "of the", "exactmetrics-premium" ),
			__( "privacy", "exactmetrics-premium" ),
			__( "rich", "exactmetrics-premium" ),
			__( "lunatic", "exactmetrics-premium" ),
			__( "off-limits", "exactmetrics-premium" ),
			__( "private", "exactmetrics-premium" ),
			__( "risky", "exactmetrics-premium" ),
			__( "lurking", "exactmetrics-premium" ),
			__( "offer", "exactmetrics-premium" ),
			__( "prize", "exactmetrics-premium" ),
			__( "ruthless", "exactmetrics-premium" ),
			__( "lust", "exactmetrics-premium" ),
			__( "official", "exactmetrics-premium" ),
			__( "luxurious", "exactmetrics-premium" ),
			__( "on the", "exactmetrics-premium" ),
			__( "profit", "exactmetrics-premium" ),
			__( "scary", "exactmetrics-premium" ),
			__( "lying", "exactmetrics-premium" ),
			__( "outlawed", "exactmetrics-premium" ),
			__( "protected", "exactmetrics-premium" ),
			__( "scream", "exactmetrics-premium" ),
			__( "searing", "exactmetrics-premium" ),
			__( "overcome", "exactmetrics-premium" ),
			__( "provocative", "exactmetrics-premium" ),
			__( "make you", "exactmetrics-premium" ),
			__( "painful", "exactmetrics-premium" ),
			__( "pummel", "exactmetrics-premium" ),
			__( "secure", "exactmetrics-premium" ),
			__( "pale", "exactmetrics-premium" ),
			__( "punish", "exactmetrics-premium" ),
			__( "marked down", "exactmetrics-premium" ),
			__( "panic", "exactmetrics-premium" ),
			__( "quadruple", "exactmetrics-premium" ),
			__( "secutively", "exactmetrics-premium" ),
			__( "massive", "exactmetrics-premium" ),
			__( "pay zero", "exactmetrics-premium" ),
			__( "seize", "exactmetrics-premium" ),
			__( "meltdown", "exactmetrics-premium" ),
			__( "payback", "exactmetrics-premium" ),
			__( "might look like a", "exactmetrics-premium" ),
			__( "peril", "exactmetrics-premium" ),
			__( "mind-blowing", "exactmetrics-premium" ),
			__( "shameless", "exactmetrics-premium" ),
			__( "minute", "exactmetrics-premium" ),
			__( "rave", "exactmetrics-premium" ),
			__( "shatter", "exactmetrics-premium" ),
			__( "piranha", "exactmetrics-premium" ),
			__( "reckoning", "exactmetrics-premium" ),
			__( "shellacking", "exactmetrics-premium" ),
			__( "mired", "exactmetrics-premium" ),
			__( "pitfall", "exactmetrics-premium" ),
			__( "reclaim", "exactmetrics-premium" ),
			__( "mistakes", "exactmetrics-premium" ),
			__( "plague", "exactmetrics-premium" ),
			__( "sick and tired", "exactmetrics-premium" ),
			__( "money", "exactmetrics-premium" ),
			__( "played", "exactmetrics-premium" ),
			__( "refugee", "exactmetrics-premium" ),
			__( "silly", "exactmetrics-premium" ),
			__( "money-grubbing", "exactmetrics-premium" ),
			__( "pluck", "exactmetrics-premium" ),
			__( "refund", "exactmetrics-premium" ),
			__( "moneyback", "exactmetrics-premium" ),
			__( "plummet", "exactmetrics-premium" ),
			__( "plunge", "exactmetrics-premium" ),
			__( "murder", "exactmetrics-premium" ),
			__( "pointless", "exactmetrics-premium" ),
			__( "sinful", "exactmetrics-premium" ),
			__( "myths", "exactmetrics-premium" ),
			__( "poor", "exactmetrics-premium" ),
			__( "remarkably", "exactmetrics-premium" ),
			__( "six-figure", "exactmetrics-premium" ),
			__( "never again", "exactmetrics-premium" ),
			__( "research", "exactmetrics-premium" ),
			__( "surrender", "exactmetrics-premium" ),
			__( "to the", "exactmetrics-premium" ),
			__( "varify", "exactmetrics-premium" ),
			__( "skyrocket", "exactmetrics-premium" ),
			__( "toxic", "exactmetrics-premium" ),
			__( "vibrant", "exactmetrics-premium" ),
			__( "slaughter", "exactmetrics-premium" ),
			__( "swindle", "exactmetrics-premium" ),
			__( "trap", "exactmetrics-premium" ),
			__( "victim", "exactmetrics-premium" ),
			__( "sleazy", "exactmetrics-premium" ),
			__( "taboo", "exactmetrics-premium" ),
			__( "treasure", "exactmetrics-premium" ),
			__( "victory", "exactmetrics-premium" ),
			__( "smash", "exactmetrics-premium" ),
			__( "tailspin", "exactmetrics-premium" ),
			__( "vindication", "exactmetrics-premium" ),
			__( "smug", "exactmetrics-premium" ),
			__( "tank", "exactmetrics-premium" ),
			__( "triple", "exactmetrics-premium" ),
			__( "viral", "exactmetrics-premium" ),
			__( "smuggled", "exactmetrics-premium" ),
			__( "tantalizing", "exactmetrics-premium" ),
			__( "triumph", "exactmetrics-premium" ),
			__( "volatile", "exactmetrics-premium" ),
			__( "sniveling", "exactmetrics-premium" ),
			__( "targeted", "exactmetrics-premium" ),
			__( "truth", "exactmetrics-premium" ),
			__( "vulnerable", "exactmetrics-premium" ),
			__( "snob", "exactmetrics-premium" ),
			__( "tawdry", "exactmetrics-premium" ),
			__( "try before you buy", "exactmetrics-premium" ),
			__( "tech", "exactmetrics-premium" ),
			__( "turn the tables", "exactmetrics-premium" ),
			__( "wanton", "exactmetrics-premium" ),
			__( "soaring", "exactmetrics-premium" ),
			__( "warning", "exactmetrics-premium" ),
			__( "teetering", "exactmetrics-premium" ),
			__( "unauthorized", "exactmetrics-premium" ),
			__( "spectacular", "exactmetrics-premium" ),
			__( "temporary fix", "exactmetrics-premium" ),
			__( "unbelievably", "exactmetrics-premium" ),
			__( "spine", "exactmetrics-premium" ),
			__( "tempting", "exactmetrics-premium" ),
			__( "uncommonly", "exactmetrics-premium" ),
			__( "what happened", "exactmetrics-premium" ),
			__( "spirit", "exactmetrics-premium" ),
			__( "what happens when", "exactmetrics-premium" ),
			__( "terror", "exactmetrics-premium" ),
			__( "under", "exactmetrics-premium" ),
			__( "what happens", "exactmetrics-premium" ),
			__( "staggering", "exactmetrics-premium" ),
			__( "underhanded", "exactmetrics-premium" ),
			__( "what this", "exactmetrics-premium" ),
			__( "that will make you", "exactmetrics-premium" ),
			__( "undo", "when you see", "exactmetrics-premium" ),
			__( "that will make", "exactmetrics-premium" ),
			__( "unexpected", "exactmetrics-premium" ),
			__( "when you", "exactmetrics-premium" ),
			__( "strangle", "exactmetrics-premium" ),
			__( "that will", "exactmetrics-premium" ),
			__( "whip", "exactmetrics-premium" ),
			__( "the best", "exactmetrics-premium" ),
			__( "whopping", "exactmetrics-premium" ),
			__( "stuck up", "exactmetrics-premium" ),
			__( "the ranking of", "exactmetrics-premium" ),
			__( "wicked", "exactmetrics-premium" ),
			__( "stunning", "exactmetrics-premium" ),
			__( "the most", "exactmetrics-premium" ),
			__( "will make you", "exactmetrics-premium" ),
			__( "stupid", "exactmetrics-premium" ),
			__( "the reason why is", "exactmetrics-premium" ),
			__( "unscrupulous", "exactmetrics-premium" ),
			__( "thing ive ever seen", "exactmetrics-premium" ),
			__( "withheld", "exactmetrics-premium" ),
			__( "this is the", "exactmetrics-premium" ),
			__( "this is what happens", "exactmetrics-premium" ),
			__( "unusually", "exactmetrics-premium" ),
			__( "wondrous", "exactmetrics-premium" ),
			__( "this is what", "exactmetrics-premium" ),
			__( "uplifting", "exactmetrics-premium" ),
			__( "worry", "exactmetrics-premium" ),
			__( "sure", "exactmetrics-premium" ),
			__( "this is", "exactmetrics-premium" ),
			__( "wounded", "exactmetrics-premium" ),
			__( "surge", "exactmetrics-premium" ),
			__( "thrilled", "exactmetrics-premium" ),
			__( "you need to know", "exactmetrics-premium" ),
			__( "thrilling", "exactmetrics-premium" ),
			__( "valor", "exactmetrics-premium" ),
			__( "you need to", "exactmetrics-premium" ),
			__( "you see what", "exactmetrics-premium" ),
			__( "surprising", "exactmetrics-premium" ),
			__( "tired", "exactmetrics-premium" ),
			__( "you see", "exactmetrics-premium" ),
			__( "surprisingly", "exactmetrics-premium" ),
			__( "to be", "exactmetrics-premium" ),
			__( "vaporize", "exactmetrics-premium" ),
		);

		return $this->emotion_power_words;
	}

	/**
	 * Power words
	 *
	 * @return array power words
	 */
	function power_words() {
		if ( isset( $this->power_words ) && ! empty( $this->power_words ) ) {
			return $this->power_words;
		}

		$this->power_words = array(
			__( "great", "exactmetrics-premium" ),
			__( "free", "exactmetrics-premium" ),
			__( "focus", "exactmetrics-premium" ),
			__( "remarkable", "exactmetrics-premium" ),
			__( "confidential", "exactmetrics-premium" ),
			__( "sale", "exactmetrics-premium" ),
			__( "wanted", "exactmetrics-premium" ),
			__( "obsession", "exactmetrics-premium" ),
			__( "sizable", "exactmetrics-premium" ),
			__( "new", "exactmetrics-premium" ),
			__( "absolutely lowest", "exactmetrics-premium" ),
			__( "surging", "exactmetrics-premium" ),
			__( "wonderful", "exactmetrics-premium" ),
			__( "professional", "exactmetrics-premium" ),
			__( "interesting", "exactmetrics-premium" ),
			__( "revisited", "exactmetrics-premium" ),
			__( "delivered", "exactmetrics-premium" ),
			__( "guaranteed", "exactmetrics-premium" ),
			__( "challenge", "exactmetrics-premium" ),
			__( "unique", "exactmetrics-premium" ),
			__( "secrets", "exactmetrics-premium" ),
			__( "special", "exactmetrics-premium" ),
			__( "lifetime", "exactmetrics-premium" ),
			__( "bargain", "exactmetrics-premium" ),
			__( "scarce", "exactmetrics-premium" ),
			__( "tested", "exactmetrics-premium" ),
			__( "highest", "exactmetrics-premium" ),
			__( "hurry", "exactmetrics-premium" ),
			__( "alert famous", "exactmetrics-premium" ),
			__( "improved", "exactmetrics-premium" ),
			__( "expert", "exactmetrics-premium" ),
			__( "daring", "exactmetrics-premium" ),
			__( "strong", "exactmetrics-premium" ),
			__( "immediately", "exactmetrics-premium" ),
			__( "advice", "exactmetrics-premium" ),
			__( "pioneering", "exactmetrics-premium" ),
			__( "unusual", "exactmetrics-premium" ),
			__( "limited", "exactmetrics-premium" ),
			__( "the truth about", "exactmetrics-premium" ),
			__( "destiny", "exactmetrics-premium" ),
			__( "outstanding", "exactmetrics-premium" ),
			__( "simplistic", "exactmetrics-premium" ),
			__( "compare", "exactmetrics-premium" ),
			__( "unsurpassed", "exactmetrics-premium" ),
			__( "energy", "exactmetrics-premium" ),
			__( "powerful", "exactmetrics-premium" ),
			__( "colorful", "exactmetrics-premium" ),
			__( "genuine", "exactmetrics-premium" ),
			__( "instructive", "exactmetrics-premium" ),
			__( "big", "exactmetrics-premium" ),
			__( "affordable", "exactmetrics-premium" ),
			__( "informative", "exactmetrics-premium" ),
			__( "liberal", "exactmetrics-premium" ),
			__( "popular", "exactmetrics-premium" ),
			__( "ultimate", "exactmetrics-premium" ),
			__( "mainstream", "exactmetrics-premium" ),
			__( "rare", "exactmetrics-premium" ),
			__( "exclusive", "exactmetrics-premium" ),
			__( "willpower", "exactmetrics-premium" ),
			__( "complete", "exactmetrics-premium" ),
			__( "edge", "exactmetrics-premium" ),
			__( "valuable", "exactmetrics-premium" ),
			__( "attractive", "exactmetrics-premium" ),
			__( "last chance", "exactmetrics-premium" ),
			__( "superior", "exactmetrics-premium" ),
			__( "how to", "exactmetrics-premium" ),
			__( "easily", "exactmetrics-premium" ),
			__( "exploit", "exactmetrics-premium" ),
			__( "unparalleled", "exactmetrics-premium" ),
			__( "endorsed", "exactmetrics-premium" ),
			__( "approved", "exactmetrics-premium" ),
			__( "quality", "exactmetrics-premium" ),
			__( "fascinating", "exactmetrics-premium" ),
			__( "unlimited", "exactmetrics-premium" ),
			__( "competitive", "exactmetrics-premium" ),
			__( "gigantic", "exactmetrics-premium" ),
			__( "compromise", "exactmetrics-premium" ),
			__( "discount", "exactmetrics-premium" ),
			__( "full", "exactmetrics-premium" ),
			__( "love", "exactmetrics-premium" ),
			__( "odd", "exactmetrics-premium" ),
			__( "fundamentals", "exactmetrics-premium" ),
			__( "mammoth", "exactmetrics-premium" ),
			__( "lavishly", "exactmetrics-premium" ),
			__( "bottom line", "exactmetrics-premium" ),
			__( "under priced", "exactmetrics-premium" ),
			__( "innovative", "exactmetrics-premium" ),
			__( "reliable", "exactmetrics-premium" ),
			__( "zinger", "exactmetrics-premium" ),
			__( "suddenly", "exactmetrics-premium" ),
			__( "it's here", "exactmetrics-premium" ),
			__( "terrific", "exactmetrics-premium" ),
			__( "simplified", "exactmetrics-premium" ),
			__( "perspective", "exactmetrics-premium" ),
			__( "just arrived", "exactmetrics-premium" ),
			__( "breakthrough", "exactmetrics-premium" ),
			__( "tremendous", "exactmetrics-premium" ),
			__( "launching", "exactmetrics-premium" ),
			__( "sure fire", "exactmetrics-premium" ),
			__( "emerging", "exactmetrics-premium" ),
			__( "helpful", "exactmetrics-premium" ),
			__( "skill", "exactmetrics-premium" ),
			__( "soar", "exactmetrics-premium" ),
			__( "profitable", "exactmetrics-premium" ),
			__( "special offer", "exactmetrics-premium" ),
			__( "reduced", "exactmetrics-premium" ),
			__( "beautiful", "exactmetrics-premium" ),
			__( "sampler", "exactmetrics-premium" ),
			__( "technology", "exactmetrics-premium" ),
			__( "better", "exactmetrics-premium" ),
			__( "crammed", "exactmetrics-premium" ),
			__( "noted", "exactmetrics-premium" ),
			__( "selected", "exactmetrics-premium" ),
			__( "shrewd", "exactmetrics-premium" ),
			__( "growth", "exactmetrics-premium" ),
			__( "luxury", "exactmetrics-premium" ),
			__( "sturdy", "exactmetrics-premium" ),
			__( "enormous", "exactmetrics-premium" ),
			__( "promising", "exactmetrics-premium" ),
			__( "unconditional", "exactmetrics-premium" ),
			__( "wealth", "exactmetrics-premium" ),
			__( "spotlight", "exactmetrics-premium" ),
			__( "astonishing", "exactmetrics-premium" ),
			__( "timely", "exactmetrics-premium" ),
			__( "successful", "exactmetrics-premium" ),
			__( "useful", "exactmetrics-premium" ),
			__( "imagination", "exactmetrics-premium" ),
			__( "bonanza", "exactmetrics-premium" ),
			__( "opportunities", "exactmetrics-premium" ),
			__( "survival", "exactmetrics-premium" ),
			__( "greatest", "exactmetrics-premium" ),
			__( "security", "exactmetrics-premium" ),
			__( "last minute", "exactmetrics-premium" ),
			__( "largest", "exactmetrics-premium" ),
			__( "high tech", "exactmetrics-premium" ),
			__( "refundable", "exactmetrics-premium" ),
			__( "monumental", "exactmetrics-premium" ),
			__( "colossal", "exactmetrics-premium" ),
			__( "latest", "exactmetrics-premium" ),
			__( "quickly", "exactmetrics-premium" ),
			__( "startling", "exactmetrics-premium" ),
			__( "now", "exactmetrics-premium" ),
			__( "important", "exactmetrics-premium" ),
			__( "revolutionary", "exactmetrics-premium" ),
			__( "quick", "exactmetrics-premium" ),
			__( "unlock", "exactmetrics-premium" ),
			__( "urgent", "exactmetrics-premium" ),
			__( "miracle", "exactmetrics-premium" ),
			__( "easy", "exactmetrics-premium" ),
			__( "fortune", "exactmetrics-premium" ),
			__( "amazing", "exactmetrics-premium" ),
			__( "magic", "exactmetrics-premium" ),
			__( "direct", "exactmetrics-premium" ),
			__( "authentic", "exactmetrics-premium" ),
			__( "exciting", "exactmetrics-premium" ),
			__( "proven", "exactmetrics-premium" ),
			__( "simple", "exactmetrics-premium" ),
			__( "announcing", "exactmetrics-premium" ),
			__( "portfolio", "exactmetrics-premium" ),
			__( "reward", "exactmetrics-premium" ),
			__( "strange", "exactmetrics-premium" ),
			__( "huge gift", "exactmetrics-premium" ),
			__( "revealing", "exactmetrics-premium" ),
			__( "weird", "exactmetrics-premium" ),
			__( "value", "exactmetrics-premium" ),
			__( "introducing", "exactmetrics-premium" ),
			__( "sensational", "exactmetrics-premium" ),
			__( "surprise", "exactmetrics-premium" ),
			__( "insider", "exactmetrics-premium" ),
			__( "practical", "exactmetrics-premium" ),
			__( "excellent", "exactmetrics-premium" ),
			__( "delighted", "exactmetrics-premium" ),
			__( "download", "exactmetrics-premium" ),
		);

		return $this->power_words;
	}

	/**
	 * Common words
	 *
	 * @return array common words
	 */
	function common_words() {
		if ( isset( $this->common_words ) && ! empty( $this->common_words ) ) {
			return $this->common_words;
		}

		$this->common_words = array(
			__( "a", "exactmetrics-premium" ),
			__( "for", "exactmetrics-premium" ),
			__( "about", "exactmetrics-premium" ),
			__( "from", "exactmetrics-premium" ),
			__( "after", "exactmetrics-premium" ),
			__( "get", "exactmetrics-premium" ),
			__( "all", "exactmetrics-premium" ),
			__( "has", "exactmetrics-premium" ),
			__( "an", "exactmetrics-premium" ),
			__( "have", "exactmetrics-premium" ),
			__( "and", "exactmetrics-premium" ),
			__( "he", "exactmetrics-premium" ),
			__( "are", "exactmetrics-premium" ),
			__( "her", "exactmetrics-premium" ),
			__( "as", "exactmetrics-premium" ),
			__( "his", "exactmetrics-premium" ),
			__( "at", "exactmetrics-premium" ),
			__( "how", "exactmetrics-premium" ),
			__( "be", "exactmetrics-premium" ),
			__( "I", "exactmetrics-premium" ),
			__( "but", "exactmetrics-premium" ),
			__( "if", "exactmetrics-premium" ),
			__( "by", "exactmetrics-premium" ),
			__( "in", "exactmetrics-premium" ),
			__( "can", "exactmetrics-premium" ),
			__( "is", "exactmetrics-premium" ),
			__( "did", "exactmetrics-premium" ),
			__( "it", "exactmetrics-premium" ),
			__( "do", "exactmetrics-premium" ),
			__( "just", "exactmetrics-premium" ),
			__( "ever", "exactmetrics-premium" ),
			__( "like", "exactmetrics-premium" ),
			__( "ll", "exactmetrics-premium" ),
			__( "these", "exactmetrics-premium" ),
			__( "me", "exactmetrics-premium" ),
			__( "they", "exactmetrics-premium" ),
			__( "most", "exactmetrics-premium" ),
			__( "things", "exactmetrics-premium" ),
			__( "my", "exactmetrics-premium" ),
			__( "this", "exactmetrics-premium" ),
			__( "no", "exactmetrics-premium" ),
			__( "to", "exactmetrics-premium" ),
			__( "not", "exactmetrics-premium" ),
			__( "up", "exactmetrics-premium" ),
			__( "of", "exactmetrics-premium" ),
			__( "was", "exactmetrics-premium" ),
			__( "on", "exactmetrics-premium" ),
			__( "what", "exactmetrics-premium" ),
			__( "re", "exactmetrics-premium" ),
			__( "when", "exactmetrics-premium" ),
			__( "she", "exactmetrics-premium" ),
			__( "who", "exactmetrics-premium" ),
			__( "sould", "exactmetrics-premium" ),
			__( "why", "exactmetrics-premium" ),
			__( "so", "exactmetrics-premium" ),
			__( "will", "exactmetrics-premium" ),
			__( "that", "exactmetrics-premium" ),
			__( "with", "exactmetrics-premium" ),
			__( "the", "exactmetrics-premium" ),
			__( "you", "exactmetrics-premium" ),
			__( "their", "exactmetrics-premium" ),
			__( "your", "exactmetrics-premium" ),
			__( "there", "exactmetrics-premium" ),
		);

		return $this->common_words;
	}


	/**
	 * Uncommon words
	 *
	 * @return array uncommon words
	 */
	function uncommon_words() {
		if ( isset( $this->uncommon_words ) && ! empty( $this->uncommon_words ) ) {
			return $this->uncommon_words;
		}

		$this->uncommon_words = array(
			__( "actually", "exactmetrics-premium" ),
			__( "happened", "exactmetrics-premium" ),
			__( "need", "exactmetrics-premium" ),
			__( "thing", "exactmetrics-premium" ),
			__( "awesome", "exactmetrics-premium" ),
			__( "heart", "exactmetrics-premium" ),
			__( "never", "exactmetrics-premium" ),
			__( "think", "exactmetrics-premium" ),
			__( "baby", "exactmetrics-premium" ),
			__( "here", "exactmetrics-premium" ),
			__( "new", "exactmetrics-premium" ),
			__( "time", "exactmetrics-premium" ),
			__( "beautiful", "exactmetrics-premium" ),
			__( "its", "exactmetrics-premium" ),
			__( "now", "exactmetrics-premium" ),
			__( "valentines", "exactmetrics-premium" ),
			__( "being", "exactmetrics-premium" ),
			__( "know", "exactmetrics-premium" ),
			__( "old", "exactmetrics-premium" ),
			__( "video", "exactmetrics-premium" ),
			__( "best", "exactmetrics-premium" ),
			__( "life", "exactmetrics-premium" ),
			__( "one", "exactmetrics-premium" ),
			__( "want", "exactmetrics-premium" ),
			__( "better", "exactmetrics-premium" ),
			__( "little", "exactmetrics-premium" ),
			__( "out", "exactmetrics-premium" ),
			__( "watch", "exactmetrics-premium" ),
			__( "boy", "exactmetrics-premium" ),
			__( "look", "exactmetrics-premium" ),
			__( "people", "exactmetrics-premium" ),
			__( "way", "exactmetrics-premium" ),
			__( "dog", "exactmetrics-premium" ),
			__( "love", "exactmetrics-premium" ),
			__( "photos", "exactmetrics-premium" ),
			__( "ways", "exactmetrics-premium" ),
			__( "down", "exactmetrics-premium" ),
			__( "made", "exactmetrics-premium" ),
			__( "really", "exactmetrics-premium" ),
			__( "world", "exactmetrics-premium" ),
			__( "facebook", "exactmetrics-premium" ),
			__( "make", "exactmetrics-premium" ),
			__( "reasons", "exactmetrics-premium" ),
			__( "year", "exactmetrics-premium" ),
			__( "first", "exactmetrics-premium" ),
			__( "makes", "exactmetrics-premium" ),
			__( "right", "exactmetrics-premium" ),
			__( "years", "exactmetrics-premium" ),
			__( "found", "exactmetrics-premium" ),
			__( "man", "exactmetrics-premium" ),
			__( "see", "exactmetrics-premium" ),
			__( "you'll", "exactmetrics-premium" ),
			__( "girl", "exactmetrics-premium" ),
			__( "media", "exactmetrics-premium" ),
			__( "seen", "exactmetrics-premium" ),
			__( "good", "exactmetrics-premium" ),
			__( "mind", "exactmetrics-premium" ),
			__( "social", "exactmetrics-premium" ),
			__( "guy", "exactmetrics-premium" ),
			__( "more", "exactmetrics-premium" ),
			__( "something", "exactmetrics-premium" ),
		);

		return $this->uncommon_words;
	}
}

new ExactMetricsHeadlineToolPlugin();
