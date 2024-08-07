<?php
	class lockHelper {

		private static $pid;

		function __construct() {}

		function __clone() {}

		private static function isrunning() {
			$pids = explode(PHP_EOL, `ps -e | awk '{print $1}'`);
			if(in_array(self::$pid, $pids))
				return TRUE;
			return FALSE;
		}

		public static function lock() {
			global $argv;

            if ($argv && isset($argv) && isset($argv[0])) {
			    $lock_file = LOCK_DIR.$argv[0].LOCK_SUFFIX;
            } else {
			    $lock_file = LOCK_DIR . LOCK_SUFFIX;
            }

			if (file_exists($lock_file)) {
				//return FALSE;

				// Is running?
				self::$pid = file_get_contents($lock_file);
				if(self::isrunning()) {
					logEntry("==".self::$pid."== Already in progress...");
					//error_log("==".self::$pid."== Already in progress...");
					return FALSE;
				}
				else {
					logEntry("==".self::$pid."== Previous job died abruptly...");
					//error_log("==".self::$pid."== Previous job died abruptly...");
				}
			}

			self::$pid = getmypid();
			file_put_contents($lock_file, self::$pid);
			logEntry("==".self::$pid."== Lock acquired, processing the job...");
			//error_log("==".self::$pid."== Lock acquired, processing the job...");
			return self::$pid;
		}

		public static function unlock() {
			global $argv;

            if ($argv && isset($argv) && isset($argv[0])) {
			    $lock_file = LOCK_DIR.$argv[0].LOCK_SUFFIX;
            } else {
			    $lock_file = LOCK_DIR . LOCK_SUFFIX;
            }

			if (file_exists($lock_file))
				unlink($lock_file);

			logEntry("==".self::$pid."== Releasing lock...");
			//error_log("==".self::$pid."== Releasing lock...");
			return TRUE;
		}

	}
?>
