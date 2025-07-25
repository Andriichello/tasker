/**
 * Generated by orval v7.10.0 🍺
 * Do not edit manually.
 * tasker
 * OpenAPI spec version: 0.1
 */
import axios from "axios";
import type { AxiosRequestConfig, AxiosResponse } from "axios";

import type {
  DestroyResponse,
  IndexTagsResponse,
  ShowTagResponse,
  StoreTagRequest,
  StoreTagResponse,
  UpdateTagRequest,
  UpdateTagResponse,
} from "../models";

/**
 * @summary Index tags.
 */
export const indexTags = <TData = AxiosResponse<IndexTagsResponse>>(
  options?: AxiosRequestConfig,
): Promise<TData> => {
  return axios.get(`/api/tags`, options);
};
/**
 * @summary Store a tag.
 */
export const storeTag = <TData = AxiosResponse<StoreTagResponse>>(
  storeTagRequest: StoreTagRequest,
  options?: AxiosRequestConfig,
): Promise<TData> => {
  return axios.post(`/api/tags`, storeTagRequest, options);
};
/**
 * @summary Show tag by id.
 */
export const showTag = <TData = AxiosResponse<ShowTagResponse>>(
  id: number,
  options?: AxiosRequestConfig,
): Promise<TData> => {
  return axios.get(`/api/tags/${id}`, options);
};
/**
 * @summary Delete tag.
 */
export const destroyTag = <TData = AxiosResponse<DestroyResponse>>(
  id: number,
  options?: AxiosRequestConfig,
): Promise<TData> => {
  return axios.delete(`/api/tags/${id}`, options);
};
/**
 * @summary Update a tag.
 */
export const updateTag = <TData = AxiosResponse<UpdateTagResponse>>(
  id: number,
  updateTagRequest: UpdateTagRequest,
  options?: AxiosRequestConfig,
): Promise<TData> => {
  return axios.patch(`/api/tags/${id}`, updateTagRequest, options);
};
export type IndexTagsResult = AxiosResponse<IndexTagsResponse>;
export type StoreTagResult = AxiosResponse<StoreTagResponse>;
export type ShowTagResult = AxiosResponse<ShowTagResponse>;
export type DestroyTagResult = AxiosResponse<DestroyResponse>;
export type UpdateTagResult = AxiosResponse<UpdateTagResponse>;
