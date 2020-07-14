<?php

namespace nogn\craftgridsome;

use Craft;
use craft\db\Query;
use craft\helpers\Json;
use craft\helpers\StringHelper;
use yii\base\InvalidArgumentException;

/**
 * Sites Controller
 *
 * @author Ben Sheedy
 * @since 1.0
 */
class SiteManager
{
    /**
     * Returns all the sites
     *
     * @return Site[]
     */
    public function getAllSites(): array
    {
        $results = $this->_createSiteQuery()
            ->all();

        return $this->_createSites($results);
    }

    /**
     * Returns a site by its ID.
     *
     * @param int $id
     * @return Site
     * @throws InvalidArgumentException if $id is invalid
     */
    public function getSiteById(int $id): Site
    {
        $result = $this->_createSiteQuery()
            ->where(['id' => $id])
            ->one();
        
        if ($result === null) {
            throw new InvalidArgumentException('Invalid site ID: ' . $id);
        }
        
        return $this->_createSite($result);
    }

    /**
     * Saves a site.
     *
     * @param Site $site
     * @param bool $runValidation
     * @return bool
     */
    public function saveSite(Site $site, bool $runValidation = true ): bool
    {
        if ($runValidation && !$site->validate()) {
            Craft::info('Site not saved due to validation error.', __METHOD__);
            return false;
        }

        $db = Craft::$app->getDb();

        if ($db->getIsMysql()) {
            $name = StringHelper::encodeMb4($site->name);
        } else {
            $name = $site->name;
        }

        $data = [
            'name' => $name,
            'url' => $site->url,
            'sectionIds' =>  $site->sectionIds ? Json::encode($site->sectionIds) : null,
        ];

        if ($site->id) {
            $db->createCommand()
                ->update('{{%nogn_sites}}', $data, ['id' => $site->id])
                ->execute();
        } else {
            $db->createCommand()
                ->insert('{{%nogn_sites}}', $data)
                ->execute();
            $site->id = $db->getLastInsertID('{{%nogn_sites}}');
        }

        return true;
    }

    /**
     * Deletes a site by its ID.
     *
     * @param int $id
     */
    public function deleteSiteById(int $id)
    {
        Craft::$app->getDb()->createCommand()
            ->delete('{{%nogn_sites}}', ['id' => $id])
            ->execute();
    }

    /**
     * @return Query
     */
    private function _createSiteQuery(): Query
    {
        return (new Query())
            ->select([
                'id',
                'name',
                'url',
                'sectionIds',
            ])
            ->from(['{{%nogn_sites}}']);
    }

        /**
     * @param array $result
     * @param bool|null $isMysql
     * @return Site
     */
    private function _createSite(array $result, bool $isMysql = null): Site
    {
        if ($isMysql ?? Craft::$app->getDb()->getIsMysql()) {
            $result['name'] = html_entity_decode($result['name'], ENT_QUOTES | ENT_HTML5);
        }

        if ($result['sectionIds']) {
            $result['sectionIds'] = Json::decode($result['sectionIds']);
        } else {
            $result['sectionIds'] = [];
        }

        return new Site($result);
    }

    /**
     * @param array
     * @return Site[]
     */
    private function _createSites(array $results): array
    {
        $sites = [];
        $isMysql = Craft::$app->getDb()->getIsMysql();

        foreach ($results as $result) {
            $sites[] = $this->_createSite($result, $isMysql);
        }

        return $sites;
    }
}

