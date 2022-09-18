<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace wcf\system\cronjob;

use wcf\data\cronjob\Cronjob;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Updates activity points.
 */
class PointsOnlinePointsCronjob extends AbstractCronjob
{
    /**
     * @inheritDoc
     */
    public function execute(Cronjob $cronjob)
    {
        parent::execute($cronjob);

        // get user having been online and add points
        $itemsToUser = [];
        $sql = "SELECT    userID 
                FROM    wcf" . WCF_N . "_user
                WHERE    uzWasOnline = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([1]);
        while ($row = $statement->fetchArray()) {
            $itemsToUser[$row['userID']] = 1;
        }
        if (\count($itemsToUser)) {
            UserActivityPointHandler::getInstance()->fireEvents('com.uz.wcf.activityPointEvent.online', $itemsToUser, true);
        }

        // remove points if not online
        $objectType = UserActivityPointHandler::getInstance()->getObjectTypeByName('com.uz.wcf.activityPointEvent.online');
        if (USER_POINTSONLINE_SUBSTRACT) {
            $itemsToUser = [];
            $sql = "SELECT        user_table.userID
                    FROM        wcf" . WCF_N . "_user user_table
                    LEFT JOIN    wcf" . WCF_N . "_user_activity_point point_table
                    ON            (user_table.userID = point_table.userID)
                    WHERE        user_table.uzWasOnline = ? AND point_table.objectTypeID = ? AND point_table.items > ?";
            $statement = WCF::getDB()->prepareStatement($sql);
            $statement->execute([0, $objectType->objectTypeID, 0]);
            while ($row = $statement->fetchArray()) {
                $itemsToUser[$row['userID']] = 1;
            }

            if (\count($itemsToUser)) {
                UserActivityPointHandler::getInstance()->removeEvents('com.uz.wcf.activityPointEvent.online', $itemsToUser);
            }
        }

        // clear uzWasOnline
        $sql = "UPDATE    wcf" . WCF_N . "_user
                SET        uzWasOnline = ?
                WHERE    uzWasOnline = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([0, 1]);
    }
}
