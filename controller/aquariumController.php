<?php
/**
 * Aquarium Controller
 * File: controller/aquariumController.php
 * Handles aquarium settings and sensor data
 */

class AquariumController {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get aquarium settings
     */
    public function getSettings($user_id) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT * FROM aquarium_settings
                WHERE user_id = ?
            ');
            $stmt->execute([$user_id]);
            $settings = $stmt->fetch();
            
            if (!$settings) {
                // Create default settings if not exist
                return $this->createDefaultSettings($user_id);
            }
            
            return [
                'success' => true,
                'data' => $settings
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create default aquarium settings
     */
    private function createDefaultSettings($user_id) {
        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO aquarium_settings (user_id, aquarium_name, fish_type, feeder_enabled, fill_enabled, drain_enabled)
                VALUES (?, ?, ?, TRUE, TRUE, TRUE)
            ');
            
            $stmt->execute([$user_id, 'My Aquarium', 'General Fish']);
            
            return $this->getSettings($user_id);
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Update aquarium settings
     */
    public function updateSettings($user_id, $aquarium_name, $fish_type, $feeder_enabled, $fill_enabled, $drain_enabled) {
        try {
            $stmt = $this->pdo->prepare('
                UPDATE aquarium_settings
                SET aquarium_name = ?, fish_type = ?, feeder_enabled = ?, fill_enabled = ?, drain_enabled = ?, updated_at = NOW()
                WHERE user_id = ?
            ');
            
            $stmt->execute([
                $aquarium_name,
                $fish_type,
                $feeder_enabled ? 1 : 0,
                $fill_enabled ? 1 : 0,
                $drain_enabled ? 1 : 0,
                $user_id
            ]);
            
            return [
                'success' => true,
                'message' => 'Settings updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Save sensor data
     */
    public function saveSensorData($user_id, $water_level, $temperature, $ph_level) {
        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO aquarium_data (user_id, water_level, temperature, ph_level)
                VALUES (?, ?, ?, ?)
            ');
            
            $stmt->execute([$user_id, floatval($water_level), floatval($temperature), floatval($ph_level)]);
            
            return [
                'success' => true,
                'message' => 'Sensor data saved',
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
     * Get latest sensor data
     */
    public function getLatestSensorData($user_id, $limit = 100) {
        try {
            $stmt = $this->pdo->prepare('
                SELECT water_level, temperature, ph_level, created_at
                FROM aquarium_data
                WHERE user_id = ?
                ORDER BY created_at DESC
                LIMIT ?
            ');
            $stmt->execute([$user_id, intval($limit)]);
            
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
}
?>
