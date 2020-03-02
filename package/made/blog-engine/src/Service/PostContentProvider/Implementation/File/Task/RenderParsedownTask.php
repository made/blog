<?php
/**
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

namespace Made\Blog\Engine\Service\PostContentProvider\Implementation\File\Task;

/**
 * Class RenderParsedownTask
 *
 * @package Made\Blog\Engine\Service\PostContentProvider\Implementation\File\Task
 */
class RenderParsedownTask
{
    // TODO: This task will need a "Parsedown" instance injected which does post-processing of the output of twig (which
    //  should be markdown). The twig rendering step is on the one hand needed as a more pretty (and namespaced) way of
    //  reading the post content files, but on the other hand it also enables pre-processing of the actual post text
    //  based on any thinkable input parameter given through the context wrapping task at the beginning of the chain.
    //  That task uses a premature callback call to be able to process the context before and after the rendering. More
    //  information on that can be found in the comment inside that task class.
    //  So, if someone decides to process the HTML output generated after this markdown parsing task for some reason,
    //  he would simply add a new task at the end of the chain, which then can do further post-processing on the output,
    //  while still maintaining the full context of the input data. An example would be running a jQuery DOM parser for
    //  analysis of how many headlines there are or some other stuff, which could prove handy when rendering on the
    //  frontend side using the embedded context (which is just and idea yet, but could save quite some extra processing).
    //  I'm not sure if the content resolution (provider based) task pipeline is the right place for that kind of stuff.
}
