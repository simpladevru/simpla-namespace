<?php

namespace Root\api;

/**
 * Simpla CMS
 *
 * @copyright     2011 Denis Pikusov
 * @link          http://simplacms.ru
 * @author        Denis Pikusov
 *
 */
class Features
{
    function get_features($filter = [])
    {
        $category_id_filter = '';
        if (isset($filter['category_id'])) {
            $category_id_filter = db()->placehold('AND id in(SELECT feature_id FROM __categories_features AS cf WHERE cf.category_id in(?@))',
                (array) $filter['category_id']);
        }

        $in_filter_filter = '';
        if (isset($filter['in_filter'])) {
            $in_filter_filter = db()->placehold('AND f.in_filter=?', intval($filter['in_filter']));
        }

        $id_filter = '';
        if (!empty($filter['id'])) {
            $id_filter = db()->placehold('AND f.id in(?@)', (array) $filter['id']);
        }

        // Выбираем свойства
        $query = db()->placehold("SELECT id, name, position, in_filter FROM __features AS f
									WHERE 1
									$category_id_filter $in_filter_filter $id_filter ORDER BY f.position");
        db()->query($query);
        return db()->results();
    }

    function get_feature($id)
    {
        // Выбираем свойство
        $query = db()->placehold("SELECT id, name, position, in_filter FROM __features WHERE id=? LIMIT 1", $id);
        db()->query($query);
        return db()->result();
    }

    function get_feature_categories($id)
    {
        $query = db()->placehold("SELECT cf.category_id as category_id FROM __categories_features cf
										WHERE cf.feature_id = ?", $id);
        db()->query($query);
        return db()->results('category_id');
    }

    public function add_feature($feature)
    {
        $query = db()->placehold("INSERT INTO __features SET ?%", $feature);
        db()->query($query);
        $id    = db()->insert_id();
        $query = db()->placehold("UPDATE __features SET position=id WHERE id=? LIMIT 1", $id);
        db()->query($query);
        return $id;
    }

    public function update_feature($id, $feature)
    {
        $query = db()->placehold("UPDATE __features SET ?% WHERE id in(?@) LIMIT ?", (array) $feature, (array) $id,
            count((array) $id));
        db()->query($query);
        return $id;
    }

    public function delete_feature($id = [])
    {
        if (!empty($id)) {
            $query = db()->placehold("DELETE FROM __features WHERE id=? LIMIT 1", intval($id));
            db()->query($query);
            $query = db()->placehold("DELETE FROM __options WHERE feature_id=?", intval($id));
            db()->query($query);
            $query = db()->placehold("DELETE FROM __categories_features WHERE feature_id=?", intval($id));
            db()->query($query);
        }
    }

    public function delete_option($product_id, $feature_id)
    {
        $query = db()->placehold("DELETE FROM __options WHERE product_id=? AND feature_id=? LIMIT 1",
            intval($product_id), intval($feature_id));
        db()->query($query);
    }

    public function update_option($product_id, $feature_id, $value)
    {
        if ($value != '') {
            $query = db()->placehold("REPLACE INTO __options SET value=?, product_id=?, feature_id=?", $value,
                intval($product_id), intval($feature_id));
        } else {
            $query = db()->placehold("DELETE FROM __options WHERE feature_id=? AND product_id=?", intval($feature_id),
                intval($product_id));
        }
        return db()->query($query);
    }

    public function add_feature_category($id, $category_id)
    {
        $query = db()->placehold("INSERT IGNORE INTO __categories_features SET feature_id=?, category_id=?", $id,
            $category_id);
        db()->query($query);
    }

    public function update_feature_categories($id, $categories)
    {
        $id    = intval($id);
        $query = db()->placehold("DELETE FROM __categories_features WHERE feature_id=?", $id);
        db()->query($query);

        if (is_array($categories)) {
            $values = [];
            foreach ($categories as $category) {
                $values[] = "($id , " . intval($category) . ")";
            }

            $query = db()->placehold("INSERT INTO __categories_features (feature_id, category_id) VALUES " . implode(', ',
                    $values));
            db()->query($query);

            // Удалим значения из options
            $query = db()->placehold("DELETE o FROM __options o
			                               LEFT JOIN __products_categories pc ON pc.product_id=o.product_id
			                               WHERE o.feature_id=? AND pc.position=(SELECT MIN(pc2.position) FROM __products_categories pc2 WHERE pc.product_id=pc2.product_id) AND pc.category_id not in(?@)",
                $id, $categories);
            db()->query($query);
        } else {
            // Удалим значения из options
            $query = db()->placehold("DELETE o FROM __options o WHERE o.feature_id=?", $id);
            db()->query($query);
        }
    }

    public function get_options($filter = [])
    {
        $feature_id_filter  = '';
        $product_id_filter  = '';
        $category_id_filter = '';
        $visible_filter     = '';
        $brand_id_filter    = '';
        $features_filter    = '';

        if (empty($filter['feature_id']) && empty($filter['product_id'])) {
            return [];
        }

        $group_by = '';
        if (isset($filter['feature_id'])) {
            $group_by = 'GROUP BY feature_id, value';
        }

        if (isset($filter['feature_id'])) {
            $feature_id_filter = db()->placehold('AND po.feature_id in(?@)', (array) $filter['feature_id']);
        }

        if (isset($filter['product_id'])) {
            $product_id_filter = db()->placehold('AND po.product_id in(?@)', (array) $filter['product_id']);
        }

        if (isset($filter['category_id'])) {
            $category_id_filter = db()->placehold('INNER JOIN __products_categories pc ON pc.product_id=po.product_id AND pc.category_id in(?@)',
                (array) $filter['category_id']);
        }

        if (isset($filter['visible'])) {
            $visible_filter = db()->placehold('INNER JOIN __products p ON p.id=po.product_id AND visible=?',
                intval($filter['visible']));
        }

        if (isset($filter['brand_id'])) {
            $brand_id_filter = db()->placehold('AND po.product_id in(SELECT id FROM __products WHERE brand_id in(?@))',
                (array) $filter['brand_id']);
        }

        if (isset($filter['features'])) {
            foreach ($filter['features'] as $feature => $value) {
                $features_filter .= db()->placehold('AND (po.feature_id=? OR po.product_id in (SELECT product_id FROM __options WHERE feature_id=? AND value=? )) ',
                    $feature, $feature, $value);
            }
        }

        $query = db()->placehold("SELECT po.product_id, po.feature_id, po.value, count(po.product_id) as count
		    FROM __options po
		    $visible_filter
			$category_id_filter
			WHERE 1 $feature_id_filter $product_id_filter $brand_id_filter $features_filter GROUP BY po.feature_id, po.value ORDER BY value=0, -value DESC, value");

        db()->query($query);
        return db()->results();
    }

    public function get_product_options($product_id)
    {
        $query = db()->placehold("SELECT f.id as feature_id, f.name, po.value, po.product_id FROM __options po LEFT JOIN __features f ON f.id=po.feature_id
										WHERE po.product_id in(?@) ORDER BY f.position", (array) $product_id);

        db()->query($query);
        return db()->results();
    }
}
