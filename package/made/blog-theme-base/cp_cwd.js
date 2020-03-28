/*
 * Made Blog
 * Copyright (c) 2019-2020 Made
 *
 * This program  is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

module.exports =
    /**
     * Determine the actual path of a symlinked folder instead of the actual directory.
     *
     * @example Folder /var/tmp is symlinked to /private/var/tmp -> this script gets the path /var/tmp
     *
     * @link https://github.com/nodejs/node-v0.x-archive/issues/2305 - process.env.PWD not provided on windows
     * @link https://stackoverflow.com/q/24112452 - __dirname, process.cwd reporting actual directory instead of symlinked one
     * @link https://github.com/nodejs/node/issues/8237 - process.cwd() inconsistency across platforms
     * @link https://github.com/nodejs/node/issues/11422 - this same problem with os.tmpdir() on macOS
     * @link https://github.com/nodejs/node/issues/7545 - working directory mismatch on macOS
     * @link https://github.com/nodejs/node-v0.x-archive/issues/18203 - Resolve module with symlinks
     * @link https://github.com/nodejs/node/blob/00c86cc8e9b5172372571cc122ebeb6d5a7e5e93/lib/os.js#L36 - Inspired from here
     *
     * @returns {string}
     */
    function cp_cwd() {
        const isWindows = process.platform === 'win32';
        let {execSync: exec} = require('child_process');

        return exec(isWindows ? 'cd' : 'pwd')
            .toString();
    }
;
