<?php

namespace App\Repositories;

use App\Models\Announcement;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AnnouncementRepository
{
    public function getAllAnnouncements(int $perPage = 10, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $queryResult = Announcement::query();

        if (!is_null($sortField) && !is_null($sortOrder)) {
            $queryResult->orderBy($sortField, $sortOrder);
        } else {
            $queryResult->orderBy("publish_at", "desc");
        }

        if (!is_null($keyword)) {
            $queryResult->search($keyword);
        }

        $paginator = $queryResult->paginate($perPage);
        $paginator->withQueryString();

        return $paginator;
    }

    public function getAnnouncementById($announcementId): ?Announcement
    {
        return Announcement::find($announcementId);
    }

    public function createAnnouncement($data)
    {
        return Announcement::create($data);
    }

    public function update($announcementId, $data)
    {
        $announcement = Announcement::find($announcementId);
        if ($announcement) {
            $announcement->update($data);
            return $announcement;
        } else {
            throw new Exception("Announcement not found");
        }
    }

    public function delete($announcementId): ?bool
    {
        try {
            $announcement = Announcement::findOrFail($announcementId);
            $announcement->delete();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getPublishedAnnouncements()
    {
        return Announcement::published()->orderBy('publish_at', 'desc')->get();
    }

    public function getUnpublishedAnnouncements()
    {
        return Announcement::unpublished()->orderBy('publish_at', 'desc')->get();
    }

    public function getActiveAnnouncements()
    {
        return Announcement::active()->orderBy('publish_at', 'desc')->get();
    }

    public function getExpiredAnnouncements()
    {
        return Announcement::expired()->orderBy('expire_at', 'desc')->get();
    }

    public function getUpcomingAnnouncements()
    {
        return Announcement::upcoming()->orderBy('publish_at', 'asc')->get();
    }

    public function getAnnouncementsForSchool($schoolId)
    {
        return Announcement::forSchool($schoolId)->orderBy('publish_at', 'desc')->get();
    }

    public function getAnnouncementsForAcademicYear($academicYearId)
    {
        return Announcement::forAcademicYear($academicYearId)->orderBy('publish_at', 'desc')->get();
    }

    public function getAnnouncementsForClass($classId)
    {
        return Announcement::forClass($classId)->orderBy('publish_at', 'desc')->get();
    }

    public function getAnnouncementsForSender($senderId)
    {
        return Announcement::forSender($senderId)->orderBy('publish_at', 'desc')->get();
    }

    public function getAnnouncementsByPriority($priority)
    {
        return Announcement::priority($priority)->orderBy('publish_at', 'desc')->get();
    }

    public function getAnnouncementsByAudienceType($audienceType)
    {
        return Announcement::audienceType($audienceType)->orderBy('publish_at', 'desc')->get();
    }

    public function getAnnouncementsSentToParents()
    {
        return Announcement::sentToParents()->orderBy('publish_at', 'desc')->get();
    }

    public function getAnnouncementsNotSentToParents()
    {
        return Announcement::notSentToParents()->orderBy('publish_at', 'desc')->get();
    }

    public function getAnnouncementsByDateRange($startDate, $endDate)
    {
        return Announcement::dateRange($startDate, $endDate)->orderBy('publish_at', 'desc')->get();
    }

    public function togglePublishedStatus($announcementId)
    {
        $announcement = Announcement::find($announcementId);
        if ($announcement) {
            $announcement->is_published = !$announcement->is_published;
            $announcement->save();
            return $announcement;
        } else {
            throw new Exception("Announcement not found");
        }
    }

    public function getLatestAnnouncements($limit = 5)
    {
        return Announcement::published()->orderBy('publish_at', 'desc')->limit($limit)->get();
    }

    public function getImportantAnnouncements($limit = 5)
    {
        return Announcement::published()->priority('high')->orWhere('priority', 'urgent')->orderBy('publish_at', 'desc')->limit($limit)->get();
    }

    public function getAnnouncementsForUser($userId)
    {
        // This is a complex query that depends on user roles, school, class, etc.
        // For simplicity, let's assume a user can see announcements for their school, academic year, and class.
        // This would require joining with UserProfile, Enrollment, etc.
        // For now, let's return all published announcements.
        return Announcement::published()->orderBy('publish_at', 'desc')->get();
    }
}
