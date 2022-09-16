<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\WCF;

/**
 * Updates uzWasOnline in the user table.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.activity.points.online
 */
class PointsOnlineActivityCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		$sql = "UPDATE	wcf".WCF_N."_user user_table,
						wcf".WCF_N."_session session
				SET		user_table.uzWasOnline = ?
				WHERE	user_table.userID = session.userID
						AND user_table.uzWasOnline = ?
						AND session.userID <> ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([1, 0, 0]);
	}
}
