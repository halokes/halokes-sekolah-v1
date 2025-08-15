<?php

namespace App\Services;

use App\Models\Announcement;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\AnnouncementRepository;

class AnnouncementService
{
    private $announcementRepository;

    /**
     * =============================================
     *  constructor
     * =============================================
     */
    public function __construct(AnnouncementRepository $announcementRepository)
    {
        $this->announcementRepository = $announcementRepository;
    }

    /**
     * =============================================
     *  list all announcements along with filter, sort, etc
     * =============================================
     */
    public function listAllAnnouncements($perPage, string $sortField = null, string $sortOrder = null, string $keyword = null): LengthAwarePaginator
    {
        $perPage = !is_null($perPage) ? $perPage : config('constant.CRUD.PER_PAGE');
        return $this->announcementRepository->getAllAnnouncements($perPage, $sortField, $sortOrder, $keyword);
    }

    /**
     * =============================================
     * get single announcement data
     * =============================================
     */
    public function getAnnouncementDetail($announcementId): ?Announcement
    {
        return $this->announcementRepository->getAnnouncementById($announcementId);
    }

    /**
     * =============================================
     * process add new announcement to database
     * =============================================
     */
    public function addNewAnnouncement(array $validatedData)
    {
        DB::beginTransaction();
        try {
            $announcement = $this->announcementRepository->createAnnouncement($validatedData);
            DB::commit();
            return $announcement;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to save new announcement to database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process update announcement data
     * =============================================
     */
    public function updateAnnouncement(array $validatedData, $id)
    {
        DB::beginTransaction();
        try {
            $announcement = $this->announcementRepository->getAnnouncementById($id);

            if (!$announcement) {
                throw new \Exception("Announcement not found");
            }

            $announcement = $this->announcementRepository->update($id, $validatedData);
            DB::commit();
            return $announcement;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to update announcement in the database: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * process delete announcement
     * =============================================
     */
    public function deleteAnnouncement($announcementId): ?bool
    {
        DB::beginTransaction();
        try {
            $result = $this->announcementRepository->delete($announcementId);
            DB::commit();
            return $result;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to delete announcement with id $announcementId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get published announcements
     * =============================================
     */
    public function getPublishedAnnouncements()
    {
        return $this->announcementRepository->getPublishedAnnouncements();
    }

    /**
     * =============================================
     * get unpublished announcements
     * =============================================
     */
    public function getUnpublishedAnnouncements()
    {
        return $this->announcementRepository->getUnpublishedAnnouncements();
    }

    /**
     * =============================================
     * get active announcements
     * =============================================
     */
    public function getActiveAnnouncements()
    {
        return $this->announcementRepository->getActiveAnnouncements();
    }

    /**
     * =============================================
     * get expired announcements
     * =============================================
     */
    public function getExpiredAnnouncements()
    {
        return $this->announcementRepository->getExpiredAnnouncements();
    }

    /**
     * =============================================
     * get upcoming announcements
     * =============================================
     */
    public function getUpcomingAnnouncements()
    {
        return $this->announcementRepository->getUpcomingAnnouncements();
    }

    /**
     * =============================================
     * get announcements for school
     * =============================================
     */
    public function getAnnouncementsForSchool($schoolId)
    {
        return $this->announcementRepository->getAnnouncementsForSchool($schoolId);
    }

    /**
     * =============================================
     * get announcements for academic year
     * =============================================
     */
    public function getAnnouncementsForAcademicYear($academicYearId)
    {
        return $this->announcementRepository->getAnnouncementsForAcademicYear($academicYearId);
    }

    /**
     * =============================================
     * get announcements for class
     * =============================================
     */
    public function getAnnouncementsForClass($classId)
    {
        return $this->announcementRepository->getAnnouncementsForClass($classId);
    }

    /**
     * =============================================
     * get announcements for sender
     * =============================================
     */
    public function getAnnouncementsForSender($senderId)
    {
        return $this->announcementRepository->getAnnouncementsForSender($senderId);
    }

    /**
     * =============================================
     * get announcements by priority
     * =============================================
     */
    public function getAnnouncementsByPriority($priority)
    {
        return $this->announcementRepository->getAnnouncementsByPriority($priority);
    }

    /**
     * =============================================
     * get announcements by audience type
     * =============================================
     */
    public function getAnnouncementsByAudienceType($audienceType)
    {
        return $this->announcementRepository->getAnnouncementsByAudienceType($audienceType);
    }

    /**
     * =============================================
     * get announcements sent to parents
     * =============================================
     */
    public function getAnnouncementsSentToParents()
    {
        return $this->announcementRepository->getAnnouncementsSentToParents();
    }

    /**
     * =============================================
     * get announcements not sent to parents
     * =============================================
     */
    public function getAnnouncementsNotSentToParents()
    {
        return $this->announcementRepository->getAnnouncementsNotSentToParents();
    }

    /**
     * =============================================
     * get announcements by date range
     * =============================================
     */
    public function getAnnouncementsByDateRange($startDate, $endDate)
    {
        return $this->announcementRepository->getAnnouncementsByDateRange($startDate, $endDate);
    }

    /**
     * =============================================
     * toggle published status
     * =============================================
     */
    public function togglePublishedStatus($announcementId): ?Announcement
    {
        DB::beginTransaction();
        try {
            $announcement = $this->announcementRepository->togglePublishedStatus($announcementId);
            DB::commit();
            return $announcement;
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error("Failed to toggle announcement published status with id $announcementId: {$exception->getMessage()}");
            throw $exception;
        }
    }

    /**
     * =============================================
     * get latest announcements
     * =============================================
     */
    public function getLatestAnnouncements($limit = 5)
    {
        return $this->announcementRepository->getLatestAnnouncements($limit);
    }

    /**
     * =============================================
     * get important announcements
     * =============================================
     */
    public function getImportantAnnouncements($limit = 5)
    {
        return $this->announcementRepository->getImportantAnnouncements($limit);
    }

    /**
     * =============================================
     * get announcements for user
     * =============================================
     */
    public function getAnnouncementsForUser($userId)
    {
        return $this->announcementRepository->getAnnouncementsForUser($userId);
    }

    /**
     * =============================================
     * get announcement dashboard data
     * =============================================
     */
    public function getAnnouncementDashboardData($schoolId = null, $academicYearId = null, $classId = null)
    {
        $data = [
            'total_announcements' => 0,
            'published_announcements' => 0,
            'unpublished_announcements' => 0,
            'active_announcements' => 0,
            'expired_announcements' => 0,
            'upcoming_announcements' => 0,
            'sent_to_parents_count' => 0,
        ];

        $query = Announcement::query();
        if ($schoolId) {
            $query->forSchool($schoolId);
        }
        if ($academicYearId) {
            $query->forAcademicYear($academicYearId);
        }
        if ($classId) {
            $query->forClass($classId);
        }
        $announcements = $query->get();

        $data['total_announcements'] = $announcements->count();
        $data['published_announcements'] = $announcements->where('is_published', true)->count();
        $data['unpublished_announcements'] = $announcements->where('is_published', false)->count();
        $data['active_announcements'] = $announcements->where('is_active', true)->count();
        $data['expired_announcements'] = $announcements->where('is_expired', true)->count();
        $data['upcoming_announcements'] = $announcements->where('publish_at', '>', now())->count();
        $data['sent_to_parents_count'] = $announcements->where('is_sent_to_parents', true)->count();

        return $data;
    }
}
