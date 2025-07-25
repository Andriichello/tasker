import { defineStore } from 'pinia';
import {
  indexTasks,
  showTask,
  storeTask,
  updateTask,
  destroyTask
} from '../api/services/tasks';
import type { Task } from '../api/models/task';
import type { StoreTaskRequest } from '../api/models/storeTaskRequest';
import type { UpdateTaskRequest } from '../api/models/updateTaskRequest';

// Helper function to load persisted state from localStorage
const loadPersistedState = (key: string) => {
  try {
    const storedState = localStorage.getItem(`pinia-${key}`);
    return storedState ? JSON.parse(storedState) : null;
  } catch (e) {
    console.error(`Error loading persisted state for ${key}:`, e);
    return null;
  }
};

// Get persisted tasks data
const persistedState = loadPersistedState('tasks');

export const useTasksStore = defineStore('tasks', {
  state: () => ({
    tasks: persistedState?.tasks || [] as Task[],
    task: persistedState?.task || null as Task | null,
    searchQuery: persistedState?.searchQuery || '',
    statusFilter: persistedState?.statusFilter || null,
    tagFilter: persistedState?.tagFilter || null,
    loading: false,
    error: null as string | null,
  }),

  getters: {
    getTasks: (state) => state.tasks,
    getTask: (state) => state.task,
    getSearchQuery: (state) => state.searchQuery,
    getStatusFilter: (state) => state.statusFilter,
    getTagFilter: (state) => state.tagFilter,
    isLoading: (state) => state.loading,
    hasError: (state) => !!state.error,
    getError: (state) => state.error,
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
      // Fetch from API if:
      // - There's a search, status, or tag filter
      // - The tasks array is empty
      // - forceReload is true (indicating filters were explicitly cleared or initial load)
      if (search || status || tag || this.tasks.length === 0 || forceReload) {
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
          this.persistState();
          return this.tasks;
        } catch (error) {
          this.error = 'Failed to fetch tasks';
          return [];
        } finally {
          this.loading = false;
        }
      }

      // Otherwise, return the cached tasks
      return this.tasks;
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
        this.persistState();
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
          this.persistState();
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

          this.persistState();
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

        this.persistState();
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
      this.persistState();
    },

    clearError() {
      this.error = null;
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
      this.persistState();
    },

    // Clear all filters
    clearFilters() {
      this.searchQuery = '';
      this.statusFilter = null;
      this.tagFilter = null;
      this.persistState();
    },

    // Persist state to localStorage
    persistState() {
      try {
        localStorage.setItem('pinia-tasks', JSON.stringify({
          tasks: this.tasks,
          task: this.task,
          searchQuery: this.searchQuery,
          statusFilter: this.statusFilter,
          tagFilter: this.tagFilter
        }));
      } catch (e) {
        console.error('Error persisting tasks state:', e);
      }
    }
  },
});
