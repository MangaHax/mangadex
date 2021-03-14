<?php

namespace Mangadex\Controller\API;

use Mangadex\Exception\Http\NotFoundHttpException;

class GroupController extends APIController
{
    public function view($path)
    {
        /**
         * @param array{0: int|string, 1: string|null, 2: int|string|mixed|null} $path
         */
        [$id, $subResource, $subResourceId] = $path;

        $id = $this->validateId($id);

        switch ($subResource) {
            case 'chapters':
                $this->fetch($id); // check if exists
                return (new ChapterController())->fetchForGroup($id);
            default:
                if (!empty($subResource)) {
                    throw new NotFoundHttpException();
                }
        }

        $group = $this->fetch($id);
        $data = $this->normalize($group);

        if (in_array('chapters', $this->request->query->getList('include'))) {
            $data = array_merge(['group' => $data], (new ChapterController())->fetchForGroup($id));
        }

        return $data;
    }

    public function fetch($id)
    {
        $group = new \Group($id);
        if (!isset($group->group_id)) {
            throw new NotFoundHttpException("Group not found.");
        }
        return $group;
    }

    private function normalize($group)
    {
        $members = $group->get_members();

        $normalized = [
            //'type' => 'group',
            'id' => $group->group_id,
            'name' => $group->group_name,
            'altNames' => $group->group_alt_name,
            'language' => $group->lang_flag,
            'leader' => [
                'id' => $group->group_leader_id,
                'name' => $group->username,
            ],
            'members' => array_map(function ($id, $user) {
                return ['id' => $id, 'name' => $user];
            }, array_keys($members), $members),
            'description' => $group->group_description,
            'website' => $group->group_website,
            'discord' => $group->group_discord ? "https://discord.gg/" . $group->group_discord : '',
            'ircServer' => $group->group_irc_server,
            'ircChannel' => $group->group_irc_channel,
            'email' => $group->group_email,
            'founded' => $group->group_founded,
            'likes' => $group->group_likes,
            'follows' => $group->group_follows,
            'views' => $group->group_views,
            'chapters' => $group->count_chapters,
            'threadId' => $group->thread_id,
            'threadPosts' => $group->thread_posts,
            'isLocked' => (bool)$group->group_control,
            'isInactive' => (bool)$group->group_is_inactive,
            'delay' => $group->group_delay,
            'lastUpdated' => $group->group_last_updated,
            'banner' => $group->group_banner ? $this->getFileUrl("/images/groups/$group->group_id.$group->group_banner") : null,
        ];

        return $normalized;
    }
}
