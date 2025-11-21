<?php
/**
 * Water Schedule Controller
 * File: controller/waterController.php
 * Handles all water fill/drain schedule operations
 */

class WaterScheduleController {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get all water fill schedules for user
     */
    public function getFillSchedules($user_id) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT id, duration, label, is_active
                FROM water_fill_schedules
                WHERE user_id = ? AND is_active = TRUE
                ORDER BY created_at DESC
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
     * Get all water drain schedules for user
     */
    public function getDrainSchedules($user_id) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT id, duration, label, is_active
                FROM water_drain_schedules
                WHERE user_id = ? AND is_active = TRUE
                ORDER BY created_at DESC
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
     * Add water fill schedule
     */
    public function addFillSchedule($user_id, $duration, $label) {
        try {
            if (empty($duration) || empty($label)) {
                throw new Exception('Duration and label required');
            }
            
            $stmt = $this->pdo->prepare('
                INSERT INTO water_fill_schedules (user_id, duration, label, is_active)
                VALUES (?, ?, ?, TRUE)
            ');
            
            $stmt->execute([$user_id, intval($duration), $label]);
            
            return [
                'success' => true,
                'message' => 'Fill schedule added successfully',
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
     * Add water drain schedule
     */
    public function addDrainSchedule($user_id, $duration, $label) {
        try {
            if (empty($duration) || empty($label)) {
                throw new Exception('Duration and label required');
            }
            
            $stmt = $this->pdo->prepare('
                INSERT INTO water_drain_schedules (user_id, duration, label, is_active)
                VALUES (?, ?, ?, TRUE)
            ');
            
            $stmt->execute([$user_id, intval($duration), $label]);
            
            return [
                'success' => true,
                'message' => 'Drain schedule added successfully',
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
     * Delete fill schedule
     */
    public function deleteFillSchedule($id, $user_id) {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE water_fill_schedules
                SET is_active = FALSE
                WHERE id = ? AND user_id = ?
            ');
            
            $stmt->execute([$id, $user_id]);
            
            return [
                'success' => true,
                'message' => 'Fill schedule deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete drain schedule
     */
    public function deleteDrainSchedule($id, $user_id) {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE water_drain_schedules
                SET is_active = FALSE
                WHERE id = ? AND user_id = ?
            ');
            
            $stmt->execute([$id, $user_id]);
            
            return [
                'success' => true,
                'message' => 'Drain schedule deleted successfully'
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
