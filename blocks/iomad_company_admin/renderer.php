<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

class block_iomad_company_admin_renderer extends plugin_renderer_base {
    
    /**
     * Display list of available roles 
     * @param array $roles
     */
    public function role_select($roles, $linkurl) {
        $table = new html_table();
        $table->head = array(
            get_string('name'),
            get_string('description'),
        );
        foreach ($roles as $role) {
            $linkurl->params(array('roleid' => $role->id));
            $row = array(
                '<b>' . $role->name . '</b>',
                $role->description,
                "<a class=\"btn btn-primary\" href=\"$linkurl\">" . get_string('edit') . "</a>",
            );
            $table->data[] = $row;
        }
        
        return html_writer::table($table);
    }
    
}