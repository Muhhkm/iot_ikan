<?php
/**
 * Feeding Schedule Controller
 * File: controller/feedingController.php
 * Handles all feeding schedule operations
 */

class FeedingScheduleController {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all feeding schedules for user
     */
    public function getSchedules($user_id) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT id, time, label, portion, days, is_active
                FROM feeding_schedules
                WHERE user_id = ? AND is_active = TRUE
                ORDER BY time ASC
            ');
            $stmt->execute([$user_id]);
            return [
                'success' => true,
                'data' => $stmt->fetchAll()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Add new feeding schedule
     */
    public function addSchedule($user_id, $time, $label, $portion, $days) {
        try {
            // Validate input
            if (empty($time) || empty($label) || empty($portion)) {
                throw new Exception('All fields required');
            }
            
            if (!is_array($days) || empty($days)) {
                throw new Exception('At least one day must be selected');
            }
            
            // Convert days array to JSON
            $daysJson = json_encode($days);
            
            $stmt = $this->pdo->prepare('
                INSERT INTO feeding_schedules (user_id, time, label, portion, days, is_active)
                VALUES (?, ?, ?, ?, ?, TRUE)
            ');
            
            $stmt->execute([$user_id, $time, $label, $portion, $daysJson]);
            
            return [
                'success' => true,
                'message' => 'Schedule added successfully',
                'id' => $this->pdo->lastInsertId()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update feeding schedule
     */
    public function updateSchedule($id, $user_id, $time, $label, $portion, $days) {
        try {
            $daysJson = json_encode($days);
            
            $stmt = $this->pdo->prepare('
                UPDATE feeding_schedules
                SET time = ?, label = ?, portion = ?, days = ?, updated_at = NOW()
                WHERE id = ? AND user_id = ?
            ');
            
            $stmt->execute([$time, $label, $portion, $daysJson, $id, $user_id]);
            
            return [
                'success' => true,
                'message' => 'Schedule updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete feeding schedule
     */
    public function deleteSchedule($id, $user_id) {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE feeding_schedules
                SET is_active = FALSE
                WHERE id = ? AND user_id = ?
            ');
            
            $stmt->execute([$id, $user_id]);
            
            return [
                'success' => true,
                'message' => 'Schedule deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
?>
