<?php
/**
 * Copyright 2018 IT Media Connect
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Itmcdev\Folium\Illuminate\Operation\Rest;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Itmcdev\Folium\Exception\InvalidArgument;
use Itmcdev\Folium\Exception\InvalidOperation;
use Itmcdev\Folium\Exception\UnspecifiedModel;
use Itmcdev\Folium\Illuminate\Operation\Operation;
use Itmcdev\Folium\Operation\Exception\Update as UpdateException;
use Itmcdev\Folium\Operation\Exception\Validation as ValidationException;
use Itmcdev\Folium\Operation\Rest\Update as UpdateInterface;
use Itmcdev\Folium\Util\ArrayUtils;
use Itmcdev\Folium\Util\CrudUtils;

/**
 * Inteface for impelenting REST Update method.
 *
 * @link https://en.wikipedia.org/wiki/Representational_state_transfer
 */
class Update  extends Operation implements UpdateInterface
{
    /**
     * Update/patch a resource in the database.
     * If multiple items are given, all patches will be applied in the given order.
     *
     * update($id, [ "text" => "I really have to iron" ])
     *
     * @param  any   $id       ID of the resource to update/patch.
     * @param  array $items    Can be a single element or an array of elements.
     * @param  array $options  To be defined.
     * @return array           Resource data.
     */
    public function update($id, array $items, array $options = []) {
        // Obtain Model Class Name and Model Primary Key
        list($modelClass, $pKey) = $this->getModelData();
        
        $criteria = [[$pKey, $id]];

        // attempt validation (if necesary)
        $this->validate($modelClass, $items, true);

        try {
            // attempt to query by criteria (convert criteria into callable code)
            $query = $this->buildQueryFromCriteria($modelClass, $criteria);
            // apply all updates
            $items = call_user_func_array('array_merge', $items);
            $query->update($items);
            // return list of primary key values
            return array_map(function ($model) use ($pKey) {
                return $model[$pKey];
            }, $query->get()->toArray());
        } catch (\Exception $e) {
            Log::error(sprintf('%s => %s', $e->__toString(), $e->getTraceAsString()));
        }

        throw new UpdateException();

    }
}
