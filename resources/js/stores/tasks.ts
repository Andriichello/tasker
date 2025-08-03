import { defineStore } from 'pinia';
import {
  indexTasks,
  showTask,
  storeTask,
  updateTask,
  destroyTask
} from '@/api';
import type { Task, StoreTaskRequest, UpdateTaskRequest  } from '@/api';

export interface TasksState {
  tasks: Task[];
  task: Task | null;
  searchQuery: string;
  statusFilter: any;
  tagFilter: any;
  loading: boolean;
  error: string | null;
}

// Local storage key for task filters
const TASK_FILTERS_STORAGE_KEY = 'tasker-filters';

// Helper function to get filters from local storage
const getFiltersFromStorage = (): { searchQuery: string, statusFilter: any, tagFilter: any } => {
  try {
    const storedFilters = localStorage.getItem(TASK_FILTERS_STORAGE_KEY);
    if (storedFilters) {
      return JSON.parse(storedFilters);
    }
  } catch (error) {
    console.error('Error reading filters from localStorage:', error);
  }
  return { searchQuery: '', statusFilter: null, tagFilter: null };
};

export const useTasksStore = defineStore('tasks', {
  state: () => {
    // Get saved filters from local storage
    const savedFilters = getFiltersFromStorage();

    return {
      tasks: [] as Task[],
      task: null as Task | null,
      searchQuery: savedFilters.searchQuery || '',
      statusFilter: savedFilters.statusFilter || null,
      tagFilter: savedFilters.tagFilter || null,
      loading: false,
      error: null as string | null,
    } as TasksState;
  },

  getters: {
    getTasks: (state) => state.tasks,
    getTask: (state) => state.task,
    getSearchQuery: (state) => state.searchQuery,
    getStatusFilter: (state) => state.statusFilter,
    getTagFilter: (state) => state.tagFilter,
    isLoading: (state) => state.loading,
    hasError: (state) => !!state.error,
    getError: (state) => state.error,
    getTags(state) {

    },
  },

  actions: {
    /**
     * Fetch tasks from the API with optional filtering
     * @param search - Optional search query
     * @param status - Optional status filter
     * @param tag - Optional tag filter
     * @param forceReload - Force reload from API even if no filters are active
     *                      This is used when filters are cleared or on initial load
     * @returns Array of tasks
     */
    async fetchTasks(search?: string, status?: string, tag?: string, forceReload: boolean = false) {
      this.loading = true;
      this.error = null;

      try {
        let params: Record<string, any> = {};
        if (search) params.search = search;
        if (status) params.status = status;
        if (tag) params.tag = tag;

        const options = Object.keys(params).length > 0 ? { params } : undefined;
        const response = await indexTasks(options);
        this.tasks = response.data.data || [];
        return this.tasks;
      } catch (error) {
        this.error = 'Failed to fetch tasks';
        return [];
      } finally {
        this.loading = false;
      }
    },

    async fetchTask(id: number) {
      // If we already have the task and it matches the requested ID, return it
      if (this.task && this.task.id === id) {
        return this.task;
      }

      // Check if the task exists in the tasks array
      const existingTask = this.tasks.find(t => t.id === id);
      if (existingTask) {
        this.task = existingTask;
        return existingTask;
      }

      // Otherwise, fetch from API
      this.loading = true;
      this.error = null;

      try {
        const response = await showTask(id);
        this.task = response.data.data || null;
        return this.task;
      } catch (error) {
        this.error = `Failed to fetch task with ID ${id}`;
        return null;
      } finally {
        this.loading = false;
      }
    },

    async createTask(taskData: StoreTaskRequest) {
      this.loading = true;
      this.error = null;

      try {
        const response = await storeTask(taskData);
        const newTask = response.data.data;

        if (newTask) {
          this.tasks = [...this.tasks, newTask];
          this.task = newTask;
        }

        return newTask;
      } catch (error) {
        this.error = 'Failed to create task';
        return null;
      } finally {
        this.loading = false;
      }
    },

    async updateTask(id: number, taskData: UpdateTaskRequest) {
      this.loading = true;
      this.error = null;

      try {
        const response = await updateTask(id, taskData);
        const updatedTask = response.data.data;

        if (updatedTask) {
          // Update the task in the tasks array
          this.tasks = this.tasks.map(task =>
            task.id === id ? updatedTask : task
          );

          // Update the current task if it's the one being updated
          if (this.task && this.task.id === id) {
            this.task = updatedTask;
          }
        }

        return updatedTask;
      } catch (error) {
        this.error = `Failed to update task with ID ${id}`;
        return null;
      } finally {
        this.loading = false;
      }
    },

    async deleteTask(id: number) {
      this.loading = true;
      this.error = null;

      try {
        await destroyTask(id);

        // Remove the task from the tasks array
        this.tasks = this.tasks.filter(task => task.id !== id);

        // Clear the current task if it's the one being deleted
        if (this.task && this.task.id === id) {
          this.task = null;
        }

        return true;
      } catch (error) {
        this.error = `Failed to delete task with ID ${id}`;
        return false;
      } finally {
        this.loading = false;
      }
    },

    clearTask() {
      this.task = null;
    },

    clearError() {
      this.error = null;
    },

    // Helper method to save filters to localStorage
    saveFiltersToStorage() {
      try {
        const filtersToSave = {
          searchQuery: this.searchQuery,
          statusFilter: this.statusFilter,
          tagFilter: this.tagFilter
        };
        localStorage.setItem(TASK_FILTERS_STORAGE_KEY, JSON.stringify(filtersToSave));
      } catch (error) {
        console.error('Error saving filters to localStorage:', error);
      }
    },

    // Update filter values
    updateFilters(searchQuery?: string, statusFilter?: any, tagFilter?: any) {
      if (searchQuery !== undefined) this.searchQuery = searchQuery;
      if (statusFilter !== undefined) {
        // Convert empty string to null for status filter
        this.statusFilter = statusFilter === '' ? null : statusFilter;
      }
      if (tagFilter !== undefined) {
        // Convert empty string to null for tag filter
        this.tagFilter = tagFilter === '' ? null : tagFilter;
      }

      // Save updated filters to localStorage
      this.saveFiltersToStorage();
    },

    // Clear all filters
    clearFilters() {
      this.searchQuery = '';
      this.statusFilter = null;
      this.tagFilter = null;

      // Remove filters from localStorage
      try {
        localStorage.removeItem(TASK_FILTERS_STORAGE_KEY);
      } catch (error) {
        console.error('Error removing filters from localStorage:', error);
      }
    }
  },
});
