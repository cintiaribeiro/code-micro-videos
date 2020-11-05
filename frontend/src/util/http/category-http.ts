import HttpResource from './http-resources';
import { httpVideo } from './index';

const categoryhttp = new HttpResource(httpVideo, "categories");
export default categoryhttp;