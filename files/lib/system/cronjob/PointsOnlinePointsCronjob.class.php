<?php
namespace wcf\system\cronjob;
use wcf\data\cronjob\Cronjob;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Updates activity points.
 * 
 * @author		2016-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.activity.points.online
 */
class PointsOnlinePointsCronjob extends AbstractCronjob {
	/**
	 * @inheritDoc
	 */
	public function execute(Cronjob $cronjob) {
		parent::execute($cronjob);
		
		// get user having been online and add points
		$itemsToUser = [];
		$sql = "SELECT	userID 
				FROM	wcf".WCF_N."_user
				WHERE	uzWasOnline = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([1]);
		while($row = $statement->fetchArray()) {
			$itemsToUser[$row['userID']] = 1;
		}
		if (count($itemsToUser)) {
			UserActivityPointHandler::getInstance()->fireEvents('com.uz.wcf.activityPointEvent.online', $itemsToUser, true);
		}
		
		// remove points if not online
		$objectType = UserActivityPointHandler::getInstance()->getObjectTypeByName('com.uz.wcf.activityPointEvent.online');
		if (USER_POINTSONLINE_SUBSTRACT) {
			$itemsToUser = [];
			$sql = "SELECT		user_table.userID
					FROM		wcf".WCF_N."_user user_table
					LEFT JOIN	wcf".WCF_N."_user_activity_point point_table
					ON			(user_table.userID = point_table.userID)
					WHERE		user_table.uzWasOnline = ? AND point_table.objectTypeID = ? AND point_table.items > ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([0, $objectType->objectTypeID, 0]);
			while($row = $statement->fetchArray()) {
				$itemsToUser[$row['userID']] = 1;
			}
			
			if (count($itemsToUser)) {
				UserActivityPointHandler::getInstance()->removeEvents('com.uz.wcf.activityPointEvent.online', $itemsToUser);
			}
		}
		
		// clear uzWasOnline
		$sql = "UPDATE	wcf".WCF_N."_user
				SET		uzWasOnline = ?
				WHERE	uzWasOnline = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([0, 1]);
	}
}
